<?php

namespace Windward\Extend;

use \Intervention\Image\ImageManagerStatic;

class Uploader extends \Windward\Core\Base
{
    
    private $rules = [];
    private $files;
    private $processOnOneError = true;
    private $hasError = false;
    private $basePath;
    private $presets = array();

    const UPLOAD_TYPE_IMG = 1;
    const UPLOAD_TYPE_FILE = 2;

    public $uploadType = self::UPLOAD_TYPE_IMG;

    public function setBasePath($path)
    {
        if (!is_dir($path)) {
            mkdir($path, 0777, true);
        }
        if (substr($path, -1) != '/') {
            $path .= '/';
        }
        $this->basePath = $path;
    }

    public function setRules(array $rules)
    {
        $this->rules = $rules;
    }

    public function handle()
    {
        $files = array();
        $post = $this->request->getPost();
        $fileIndex = 0;
        foreach ($_FILES as $key => $file) {
            if (!is_array($file['name']) && !is_array($file['tmp_name'])) {
                $file['config_rule_name'] = $key;
                $file['error'] = '';
                $file['dest'] = array();
                $file['index'] = $fileIndex++;
                $this->processFile($file, $post, $key);
                $files[$key] = $file;
            } else {
                $count = count($file['name']);
                for ($i=0; $i < $count; $i++) {
                    $one = array(
                        'name' => $file['name'][$i],
                        'type' => $file['type'][$i],
                        'tmp_name' => $file['tmp_name'][$i],
                        'error' => $file['error'][$i],
                        'size' => $file['size'][$i],
                        'config_rule_name' => $key . '[*]',
                        'error' => '',
                        'dest' => array(),
                    );
                    $one['index'] = $fileIndex++;
                    $this->processFile($one, $post, $key);
                    $files[$key][$i] = $one;
                }
            }
        }
        $this->files = $files;
    }
    
    public function getSavePath($rules, $post, $key)
    {
        $date = date('Y/m/d');
        if (isset($post['prefix']) && $post['prefix'] && is_array($post['prefix'])) {
            foreach ($post['prefix'] as $one => $value) {
                $value = preg_replace('#[^a-z0-9/]#i', '', $value);
                if (!preg_match('#^([a-zA-z0-1_*]+)$#', $one)) {
                    continue;
                }
                $one = str_replace('*', '.*', $one);
                if (preg_match("#^{$one}$#", $key)) {
                    return $value . DIRECTORY_SEPARATOR . $date . DIRECTORY_SEPARATOR;
                }
            }
        }
        if (isset($rules['savePath'])) {
            return $rules['savePath'] . DIRECTORY_SEPARATOR . $date . DIRECTORY_SEPARATOR;
        }
        return date('Y/m/d');
    }
    
    public function processFile(&$file, $post, $fileKey)
    {
        $rules = $this->getFileRules($file['config_rule_name']);
        $savePath = $this->getSavePath($rules, $post, $fileKey);
        $valid = $this->validFile($file, $rules);
        if ($valid === false) {
            $this->hasError = true;
        }
        if ($this->hasError === true) {
            return false;
        }
        $dir = $this->basePath . $savePath;
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $destName = str_replace('//', '/', $savePath . '/' . $this->getDestName($file));
        $destFileName = $this->basePath . $destName;
        if ($this->uploadType == self::UPLOAD_TYPE_IMG) {
            $exifData = exif_read_data($file['tmp_name']);
            $img = ImageManagerStatic::make($file['tmp_name']);
            if (!empty($exifData['Orientation'])) {
                switch ($exifData['Orientation']) {
                    case 8:
                        $img->rotate(90);
                        break;
                    case 3:
                        $img->rotate(180);
                        break;
                    case 6:
                        $img->rotate(-90);
                        break;
                }
            }
            $img->save($destFileName);
        } else {
            move_uploaded_file($file['tmp_name'], $destFileName);
        }
        if (!file_exists($destFileName)) {
            $file['error'] .= 'move error';
            return false;
        }
        if (isset($rules['thumbs'])) {
            $this->generateThubms($destFileName, $rules['thumbs']);
        }
        $file['dest'] = array(
            'file_name' => $destName,
            'url' => str_replace('//', '/', $this->request->getSchemaHost() . '/' . $destName),
        );
    }

    public function generateThubms($file, $thumbs)
    {
        foreach ($thumbs as $thumb) {
            if (!is_array($thumb) || (!isset($thumb['w']) && !isset($thumb['h']))) {
                continue;
            }
            $this->generateThubm($file, $thumb);
        }
    }

    public function generateThubm($file, $thumb, $name = null, $output = false)
    {
        if ($thumb['w'] > 2048) {
            $thumb['w'] = 2048;
        }
        if ($thumb['h'] > 2048) {
            $thumb['h'] = 2048;
        }
        $pathInfo = pathinfo($file);
        $thumbName = $file . '_';
        if (is_null($name)) {
            if (!isset($thumb['w'])) {
                $thumb['w'] = null;
            } else {
                $thumbName .= 'w' . $thumb['w'];
            }
            if (!isset($thumb['h'])) {
                $thumb['h'] = null;
            } else {
                $thumbName .= 'h' . $thumb['h'];
            }
        } else {
            $thumbName .= $name;
        }
        $thumbName .= '.' . $pathInfo['extension'];
        try {
            if ($output) {
                header('Content-Type: */*');
                if (!file_exists($thumbName)) {
                    $img = ImageManagerStatic::make($file);
                    $img->fit($thumb['w'], $thumb['h'])->save($thumbName);
                    exit($img->encode($pathInfo['extension']));
                }
                $fp = fopen($thumbName, 'rb');
                fpassthru($fp);
                exit;
            }
            $img = ImageManagerStatic::make($file);
            $img->fit($thumb['w'], $thumb['h'])->save($thumbName);
        } catch (Exception $e) {
            if ($this->logger) {
                $this->logger->log('api', 'Exception', $e);
            }
        }
    }

    public function getDestName($file)
    {
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $name = uniqid('upload_');
        return $name . '.' . $ext;
    }

    public function parseSize($size)
    {
        $sizeMap = array(
            'k' => 1024,
            'm' => 1024 * 1024,
            'g' => 1024 * 1024 * 1024,
        );
        $s = strtolower(substr($size, -1));
        $p = (float)substr($size, 0, -1);
        return $p * $sizeMap[$s];
    }

    public function getFileRules($validRuleName)
    {
        foreach ($this->rules as $key => $rule) {
            if ($key == $validRuleName || preg_match("~^{$key}$~", $validRuleName)) {
                return $rule;
            }
        }
        return null;
    }

    public function validFile(&$file, $rules)
    {
        if (!$rules) {
            return true;
        }
        $pathinfo = pathinfo($file['name']);
        $errors = array();
        if (isset($rules['maxSize'])) {
            $maxSize = $this->parseSize($rules['maxSize']);
            if ($file['size'] > $maxSize) {
                $errors[] = str_replace(':max', $rules['maxSize'], $rules['messageSize']);
            }
        }
        if (isset($rules['allowedTypes']) && !in_array($file['type'], $rules['allowedTypes'])) {
            $errors[] = str_replace(':types', join('、', $rules['allowedTypes']), $rules['messageType']);
        }
        if (isset($rules['allowedExts']) && !in_array($pathinfo['extension'], $rules['allowedExts'])) {
            $errors[] = str_replace(':exts', join('、', $rules['allowedExts']), $rules['messageExt']);
        }
        if (!empty($errors)) {
            $file['error'] = join("\n", $errors);
            return false;
        }
        return true;
    }

    public function getFiles()
    {
        $success = [];
        $errors = [];
        foreach ($this->files as $file) {
            if (!empty($file['error'])) {
                $errors[] = [
                    'name' => $file['name'],
                    'error' => $file['error'],
                    'index' => $file['index'],
                ];
            } else if (!empty($file['dest'])) {
                $file['dest']['name'] = $file['name'];
                $file['dest']['size'] = $file['size'];
                $file['dest']['type'] = $file['type'];
                $success[] = $file['dest'];
            } else {
                foreach ($file as $one) {
                    if (empty($one['error'])) {
                        $one['dest']['name'] = $one['name'];
                        $one['dest']['size'] = $one['size'];
                        $one['dest']['type'] = $one['type'];
                        $success[] = $one['dest'];
                    } else {
                        $errors[] = [
                            'name' => $one['name'],
                            'error' => $one['error'],
                            'index' => $one['index'],
                        ];
                    }
                }
            }
        }
        return ['success' => $success, 'errors' => $errors];
    }

    public function outImage($file)
    {
        $flg = true;
        $thumb = array();
        $name = null;
        if ($this->presets) {
            $patterns = array();
            foreach (array_keys($this->presets) as $key) {
                $patterns[] = preg_quote($key);
            }
            $pattern = '~(' . join('|', $patterns) . ')~';
            preg_match($pattern, $file, $m);
            if (preg_match($pattern, $file, $m) && isset($this->presets[$m[1]])) {
                $flg = false;
                $thumb = $this->presets[$m[1]];
                $name = $m[1];
                $file = preg_replace('~_(' . join('|', $patterns) . ').(png|jpg|jpeg)~', '', $this->basePath . $file);
            }
        }
        if ($flg) {
            if (($count = preg_match('#\.(?:png|jpg|jpeg)_(w?)(\d*)(h?)(\d*)#i', $file, $m)) == 0) {
                header('Content-Type: */*');
                $fp = fopen($this->basePath . $file, 'rb');
                fpassthru($fp);
                exit;
            }
            if ($m[1] && $m[2]) {
                $thumb[$m[1]] = $m[2];
            }
            if ($m[3] && $m[4]) {
                $thumb[$m[3]] = $m[4];
            }
            $file = preg_replace('#_(?:([w|h]\d+){1,2}.?(png|jpg|jpeg)?)$#i', '', $this->basePath . $file);
        }
        $this->generateThubm($file, $thumb, $name, true);
    }


    public function setPresets(array $presets)
    {
        $this->presets = $presets;
    }
}
