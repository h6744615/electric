<?php

namespace Windward\Core;

use Windward\Extend\Util;

class Language
{

    private static $baseDir;
    private $error;
    private $validator;
    private $info;
    private $lang = 'cn';

    public static function setBaseDir($baseDir)
    {
        self::$baseDir = $baseDir;
    }

    public function __construct($lang = null)
    {
        if ($lang && is_dir(self::$baseDir . $lang)) {
            $this->lang = $lang;
        }
    }

    public function error($key, $vars = array())
    {
        if (is_null($this->error)) {
            include self::$baseDir . $this->lang . '/' . 'error.php';
            $this->error = $error;
        }
        if (!Util::issetArrayValue($this->error, $key)) {
            return null;
        }
        $error = Util::getArrayValue($this->error, $key);
        if ($vars) {
            $error['msg'] = vsprintf($error['msg'], $vars);
        }
        return $error;
    }

    public function info($key)
    {
        if (is_null($this->info)) {
            include self::$baseDir . $this->lang . '/' . 'info.php';
            $this->info = $info;
        }
        if (!Util::issetArrayValue($this->info, $key)) {
            return null;
        }
        return Util::getArrayValue($this->info, $key);
    }

    public function validator($className, &$errors)
    {
        if (is_null($this->validator)) {
            $validator = include self::$baseDir . $this->lang . '/' . 'validator.php';
            $this->validator = $validator;
        }
        foreach ($errors as $key => $one) {
            $k = $className . '.' . $one;
            if (Util::issetArrayValue($this->validator, $k)) {
                $errors[$key] = Util::getArrayValue($this->validator, $k);
            }
        }
    }
}
