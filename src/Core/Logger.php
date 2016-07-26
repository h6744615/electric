<?php

namespace Windward\Core;

class Logger extends Base
{

    private $fileExtension = '.log';
    private $filePermission = 0777;
    private $endOfLine = PHP_EOL;
    private $baseDir;
    private $currentDay;
    private $fileResources = [];
    private $dateFormat = 'Y-m-d';
    private $uniqueId = "";

    public function __construct(Container $container, $baseDir)
    {
        parent::__construct($container);
        $this->baseDir = $baseDir;
        $this->uniqueId = uniqid();
    }

    public function getFileName($type)
    {
        return $this->baseDir . DIRECTORY_SEPARATOR . date($this->dateFormat)
                . DIRECTORY_SEPARATOR . $type . $this->fileExtension;
    }

    public function getFileResource($type)
    {
        $old = umask(0);
        $today = date($this->dateFormat);
        $file = $this->getFileName($type);
        if (!isset($this->currentDay[$type]) || $today !== $this->currentDay[$type]) {
            $dir = dirname($file);
            if (!is_dir($dir)) {
                @mkdir($dir, $this->filePermission, true);
            }
            if (isset($this->fileResources[$type]) && is_resource($this->fileResources[$type])) {
                fclose($this->fileResources[$type]);
                unset($this->fileResources[$type]);
            }
            $this->currentDay = $today;
        }
        if (!isset($this->fileResources[$type]) || !is_resource($this->fileResources[$type])) {
            $this->fileResources[$type] = fopen($file, 'a');
            try {
                $res = chmod($file, 0777);
                if (false === $res) {
                    throw new \Exception('log error');
                }
            } catch (\Exception $e) {
                $this->log('exception', (string)$e);
            }
        }
        umask($old);
        return $this->fileResources[$type];
    }

    public function log()
    {
        $args = func_get_args();
        $type = array_shift($args);
        $fileResource = $this->getFileResource($type);
        // prefix
        $prefix = $this->uniqueId . ' [' . date('Y-m-d H:i:s') . ']' . $this->endOfLine;

        // log
        flock($fileResource, LOCK_EX);

        fwrite($fileResource, $prefix);

        foreach ($args as $arg) {
            if (is_string($arg)) {
                fwrite($fileResource, preg_replace('# +#', ' ', $arg));
            } else {
                fwrite(
                    $fileResource,
                    json_encode(
                        $arg,
                        JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT
                    )
                );
            }
            fwrite($fileResource, $this->endOfLine);
        }

        fwrite($fileResource, $this->endOfLine . $this->endOfLine);

        flock($fileResource, LOCK_UN);
    }

    public function __destruct()
    {
        foreach ($this->fileResources as $fileResource) {
            if ($fileResource && is_resource($fileResource)) {
                fclose($fileResource);
            }
        }
    }
}
