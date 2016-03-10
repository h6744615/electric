<?php

namespace Windward\Core;

class Logger extends Base {

    private $fileExtension = '.log';
    private $filePermission = 0777;
    private $fileResource = null;
    private $endOfLine = PHP_EOL;
    private $baseDir;
    private $type;
    private $currentDay;
    private $dateFormat = 'Y-m-d';

    public function __construct(Container $container, $baseDir, $type) {
        parent::__construct($container);
        $this->baseDir = $baseDir;
        $this->type = $type;
    }

    public function getFileName() {
        return $this->baseDir . DIRECTORY_SEPARATOR . date($this->dateFormat)
                . DIRECTORY_SEPARATOR . $this->type . $this->fileExtension;
    }

    function log() {
        $today = date($this->dateFormat);
        if ($today !== $this->currentDay) {
            $file = $this->getFileName();
            $dir = dirname($file);
            if (!is_dir($dir)) {
                @mkdir($dir, $this->filePermission, true);
            }
            if (is_resource($this->fileResource)) {
                fclose($this->fileResource);
            }
            $this->fileResource = fopen($file, 'a');
            chmod($file, $this->filePermission);
            $this->currentDay = $today;
        }
        // prefix
        $prefix = '[' . date('Y-m-d H:i:s') . ']' . $this->endOfLine;

        // log
        flock($this->fileResource, LOCK_EX);

        fwrite($this->fileResource, $prefix);
        $args = func_get_args();

        foreach ($args as $arg) {
            if (is_string($arg)) {
                fwrite($this->fileResource, preg_replace('# +#', ' ', $arg));
            } else {
                fwrite($this->fileResource,
                        json_encode($arg,
                                JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            }
            fwrite($this->fileResource, $this->endOfLine);
        }

        fwrite($this->fileResource, $this->endOfLine . $this->endOfLine);

        flock($this->fileResource, LOCK_UN);
    }

    public function __destruct() {
        if (is_resource($this->fileResource)) {
            fclose($this->fileResource);
            $this->fileResource = null;
        }
    }

}

?>