<?php

namespace Windward\Core;

use Windward\Extend\Util;

class Language {
    
    private static $baseDir;

    private $component;

    private $error;
    private $info;

    private $lang = 'cn';

    public static function setBaseDir($baseDir)
    {
        self::$baseDir = $baseDir;
    }

    public function __construct($lang = null) {
        if ($lang && is_dir(self::$baseDir . $lang)) {
            $this->lang = $lang;
        }
    }
    
    public function error($key) {
        if (is_null($this->error)) {
            include self::$baseDir . $this->lang . '/' . 'error.php';
            $this->error = $error;
        }
        if (!Util::issetArrayValue($this->error, $key)) {
            return null;
        }
        return Util::getArrayValue($this->error, $key);
    }
    
    public function info($key) {
        if (is_null($this->info)) {
            include self::$baseDir . $this->lang . '/' . 'info.php';
            $this->info = $info;
        }
        if (!Util::issetArrayValue($this->info, $key)) {
            return null;
        }
        return Util::getArrayValue($this->info, $key);
    }
}