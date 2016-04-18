<?php

namespace Windward\Extend;

use \Intervention\Image\ImageManagerStatic;

class Uploader extends \Windward\Core\Base {
    
    private $rules;
    private $files;
    private $processOnOneError = true;
    private $hasError = false;
    private $basePath;
    private $presets = array();

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
        foreach ($_FILES as $key => $file) {
            if (!is_array($file['name']) && !is_array($file['tmp_name'])) {
                $file['config_rule_name'] = $key;
                $file['error'] = '';
                $file['dest'] = array();
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
        if ($this->hasError === true && $this->processOnOneError === false) {
            return false;
        }
        $dir = $this->basePath . $savePath;
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $destName = str_replace('//', '/', $savePath . '/' . $this->getDestName($file['name']));
        $destFileName = $this->basePath . $destName;
        move_uploaded_file($file['tmp_name'], $destFileName);
        if (!file_exists($destFileName)) {
            $file['error'] .= 'move error';
        }
        if (isset($rules['thumbs'])) {
            $this->generateThubms($destFileName, $rules['thumbs']);
        }
        $file['dest'] = array(
            'file_name' => $destName,
            'url' => $this->request->getSchemaHost() . '/' . $destName,
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

        if ($output) {
            header('Content-Type: */*');
            if (!file_exists($thumbName)) {
                $img = ImageManagerStatic::make($file);
                $img->fit($thumb['w'], $thumb['h'])->save($thumbName);
                exit($img->encode($pathInfo['extension']));
            }
            $fp = fopen($thumbName, 'rb');
            fpassthru($fp);
        }
        $img = ImageManagerStatic::make($file);
        $img->fit($thumb['w'], $thumb['h'])->save($thumbName);
    }

    public function getDestName($name)
    {
        $ext = pathinfo($name, PATHINFO_EXTENSION);
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
        $file['error'] = join("\n", $errors);
        return true;
    }

    public function getFiles()
    {
        return $this->files;
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
            if (($count = preg_match_all('#([w|h])(\d+)#i', $file, $m)) == 0) {
                return false;
            }
            for ($i = 0; $i < $count; $i++) {
                $thumb[$m[1][$i]] = $m[2][$i];
            }
            $file = preg_replace('#_(?:([w|h]\d+){1,2}.?(png|jpg|jpeg)?)#i', '', $this->basePath . $file);
        }
        $this->generateThubm($file, $thumb, $name, true);   
    }


    public function setPresets(array $presets)
    {
        $this->presets = $presets;
    }
}