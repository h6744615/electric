<?php

namespace Windward\Core;

class Logger extends Base {

    private $fileExtension = '.log';
    private $filePermission = 0777;
    private $endOfLine = PHP_EOL;
    private $baseDir;
    private $currentDay;
    private $fileResources = [];
    private $dateFormat = 'Y-m-d';

    public function __construct(Container $container, $baseDir) {
        parent::__construct($container);
        $this->baseDir = $baseDir;
    }

    public function getFileName($type) {
        return $this->baseDir . DIRECTORY_SEPARATOR . date($this->dateFormat)
                . DIRECTORY_SEPARATOR . $type . $this->fileExtension;
    }

    public function getFileResource($type) {
        $today = date($this->dateFormat);
        $file = $this->getFileName($type);
        if (!isset($this->currentDay[$type]) || $today !== $this->currentDay[$type]) {
            $dir = dirname($file);
            if (!is_dir($dir)) {
                @mkdir($dir, $this->filePermission, true);
            }
            if (isset($this->fileResources[$type]) && is_resource($this->fileResource)) {
                fclose($this->fileResource[$type]);
                unset($this->fileResources[$type]);
            }
            $this->currentDay = $today;
        }
        if (!isset($this->fileResources[$type]) || !is_resource($this->fileResources[$type])) {
            $this->fileResources[$type] = fopen($file, 'a');
            chmod($file, 0777);
        }
        return $this->fileResources[$type];
    }

    function log() {
        $args = func_get_args();
        $type = array_shift($args);
        $fileResource = $this->getFileResource($type);
        // prefix
        $prefix = '[' . date('Y-m-d H:i:s') . ']' . $this->endOfLine;

        // log
        flock($fileResource, LOCK_EX);

        fwrite($fileResource, $prefix);

        foreach ($args as $arg) {
            if (is_string($arg)) {
                fwrite($fileResource, preg_replace('# +#', ' ', $arg));
            } else {
                fwrite($fileResource,
                       json_encode($arg,
                                   JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            }
            fwrite($fileResource, $this->endOfLine);
        }

        fwrite($fileResource, $this->endOfLine . $this->endOfLine);

        flock($fileResource, LOCK_UN);
    }

    public function __destruct() {
        foreach ($this->fileResources as $fileResource) {
            if ($fileResource && is_resource($fileResource)) {
                fclose($fileResource);
            }
        }
    }

}

?>