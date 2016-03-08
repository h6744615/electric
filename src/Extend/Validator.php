<?php

namespace Windward\Extend;

use Windward\Extend\Util;

class Validator extends \Windward\Core\Base
{

    var $error = null;
    var $data = null;

    function getError($field = null)
    {
        if (is_null($field)) {
            return $this->error;
        } else {
            return Util::getArrayValue($this->error, $field);
        }
    }

    function setError($field, $error)
    {
        $this->error[$field] = $error;
    }

    function validate($config, $data, $validAll = true)
    {
        $this->data = $data;

        if (empty($config)) {
            return true;
        }

        foreach ($config as $field => $rules) {
            if (!is_array($rules)) {
                continue;
            }

            // regex
            if (preg_match('/^\/\^(.*?)\$\/([is]*)$/i', $field)) {
                foreach ($this->data as $k => $v) {
                    if (preg_match($field, $k)) {
                        $this->handle($k, $rules);
                    }
                }

                continue;
            }

            // normal
            if (!isset($this->data[$field])) {
                $this->data[$field] = null;
            }

            $this->handle($field, $rules);

            // check
            if (!$validAll && $this->error) {
                return false;
            }
        }

        return $this->error ? false : true;
    }

    function handle($field, $rules)
    {
        if (empty($rules)) {
            return false;
        }

        foreach ($rules as $v) {
            // func & msg
            if (count($v) < 2) {
                continue;
            }

            $func = $v[0];
            $error = $v[1];

            // vars
            $vars = null;

            if (isset($v[2])) {
                $vars = $v[2];
            }

            if (method_exists($this, $func)) {
                if ($func != 'isNotNull' && strlen($this->data[$field]) == 0) {
                    continue;
                }

                if (!$this->$func($this->data[$field], $vars)) {
                    $this->setError($field, $error);
                    break;
                }
            }
        }

        return true;
    }

    public function isMobile($data)
    {
        $regex = '/^1[0-9]{10}$/';
        return preg_match($regex, $data);
    }

    public function isNumber($data)
    {
        $regex = '/^[0-9]+$/';
        return preg_match($regex, $data);
    }
    
    public function isNotNull($data) {
        return $data ? true : false;
    }

    public function isMail($data)
    {
        $regex = "/^[a-zA-Z0-9_\-\.]+@[a-zA-Z0-9\._\-]*\.[a-zA-Z0-9\._\-]*$/is";
        return preg_match($regex, $data);
    }

    public function isNumeric($data)
    {
        $regex = '/^[1-9]+[0-9]*$/';
        return preg_match($regex, $data);
    }

    public function isFloat($data)
    {
        $regex = '/^([1-9]+[0-9]*$)|([0-9]{1}\.[0-9]+$)|([1-9]+[0-9]*\.[0-9]+)$/';
        return preg_match($regex, $data);
    }

    public function isDate($data)
    {
        $regex = "/^(19|20)[0-9]{2}\-[0-1]{1}[0-9]{1}\-[0-3]{1}[0-9]{1}$/";

        if (!preg_match($regex, $data)) {
            return false;
        }

        $time = strtotime($data);
        return (date('Y-m-d', $time) !== $data) ? false : true;
    }

    public function isDateTime($data)
    {
        $regex = "/^(19|20)[0-9]{2}\-[0-1]{1}[0-9]{1}\-[0-3]{1}[0-9]{1}\s+[0-2]{1}[0-9]{1}:[0-5]{1}[0-9]{1}:[0-5]{1}[0-9]{1}$/";

        if (!preg_match($regex, $data)) {
            return false;
        }

        $time = strtotime($data);
        return (date('Y-m-d H:i:s', $time) !== $data) ? false : true;
    }

    public function isShortDateTime($data)
    {
        $regex = "/^(19|20)[0-9]{2}\-[0-1]{1}[0-9]{1}\-[0-3]{1}[0-9]{1}\s+[0-2]{1}[0-9]{1}:[0-5]{1}[0-9]{1}$/";

        if (!preg_match($regex, $data)) {
            return false;
        }

        $time = strtotime($data);
        return (date('Y-m-d H:i', $time) !== $data) ? false : true;
    }

    public function isHour($data)
    {
        $regex = "/^[0-9]{1,2}$/";

        if (!preg_match($regex, $data)) {
            return false;
        } else {
            return ($data >= 0 && $data < 24) ? true : false;
        }
    }

    public function isPassword($data)
    {
        $regex = '/^[0-9a-z]{6,12}$/';
        return preg_match($regex, $data);
    }

}
