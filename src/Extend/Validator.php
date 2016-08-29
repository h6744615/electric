<?php

namespace Windward\Extend;

use Windward\Extend\Util;

class Validator extends \Windward\Core\Base
{

    public $error = null;
    public $data = null;

    public function getError($field = null)
    {
        if (is_null($field)) {
            return $this->error;
        } else {
            return Util::getArrayValue($this->error, $field);
        }
    }

    public function setError($field, $error)
    {
        $this->error[$field] = $error;
    }

    public function validate($config, $data, $validAll = true)
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

    public function handle($field, $rules)
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
                /*
                if ($func != 'isNotNull' && strlen($this->data[$field]) == 0) {
                    continue;
                }
                */
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
    
    public function isNotNull($data)
    {
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
        $regex = '#^[[:graph:] ]{6,16}$#';
        return preg_match($regex, $data);
    }
    
    public function isEarlyDate($date, $vars)
    {
        $compareWithDate = $vars['compareWith'];
        $compareModel = $vars['mode'];
        $date = date("Y-m-d", strtotime($date));
        $compareWithDate = date("Y-m-d", strtotime($compareWithDate));

        if ($compareModel == 'le') {
            return strtotime($date) <= strtotime($compareWithDate);
        }

        return strtotime($date) < strtotime($compareWithDate);
    }
    
    public function isLaterDate($date, $vars)
    {
        $compareWithDate = $vars['compareWith'];
        $compareModel = $vars['mode'];
        $date = date("Y-m-d", strtotime($date));
        $compareWithDate = date("Y-m-d", strtotime($compareWithDate));
        
        if ($compareModel == 'ge') {
            return strtotime($date) >= strtotime($compareWithDate);
        }
        
        return strtotime($date) > strtotime($compareWithDate);
    }
    
    public function isEarlyDatetime($date, $vars)
    {
        $compareWithDate = $vars['compareWith'];
        $compareModel = $vars['mode'];
        $date = date("Y-m-d H:i:s", strtotime($date));
        $compareWithDate = date("Y-m-d H:i:s", strtotime($compareWithDate));

        if ($compareModel == 'le') {
            return strtotime($date) <= strtotime($compareWithDate);
        }

        return strtotime($date) < strtotime($compareWithDate);
    }
    
    public function isLaterDatetime($date, $vars)
    {
        $compareWithDate = $vars['compareWith'];
        $compareModel = $vars['mode'];
        $date = date("Y-m-d H:i:s", strtotime($date));
        $compareWithDate = date("Y-m-d H:i:s", strtotime($compareWithDate));
        
        if ($compareModel == 'ge') {
            return strtotime($date) >= strtotime($compareWithDate);
        }
        
        return strtotime($date) > strtotime($compareWithDate);
    }
    
    /*
     * 验证身份证号码
     */

    public function isIdNumber($idnumber)
    {
        if (empty($idnumber)) {
            return true;
        }
        $partten = '/^[\d]{6}((19[\d]{2})|(200[0-8]))((0[1-9])|(1[0-2]))((0[1-9])|([12][\d])|(3[01]))[\d]{3}[0-9xX]$/';
        if (preg_match($partten, $idnumber)) {
            $truenum = substr($idnumber, 0, 17);
            $nsum = $truenum[0] * 7;
            $nsum += $truenum[1] * 9;
            $nsum += $truenum[2] * 10;
            $nsum += $truenum[3] * 5;
            $nsum += $truenum[4] * 8;
            $nsum += $truenum[5] * 4;
            $nsum += $truenum[6] * 2;
            $nsum += $truenum[7] * 1;
            $nsum += $truenum[8] * 6;
            $nsum += $truenum[9] * 3;
            $nsum += $truenum[10] * 7;
            $nsum += $truenum[11] * 9;
            $nsum += $truenum[12] * 10;
            $nsum += $truenum[13] * 5;
            $nsum += $truenum[14] * 8;
            $nsum += $truenum[15] * 4;
            $nsum += $truenum[16] * 2;
            $yzm = 12 - $nsum % 11;
            if ($yzm == 10) {
                $yzm = 'x';
            } elseif ($yzm == 12) {
                $yzm = '1';
            } elseif ($yzm == 11) {
                $yzm = '0';
            }
            if (strtolower($idnumber[17]) == $yzm) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    /*
     * 是否汉字
     */
    public function isChineseWord($str, $maxlength = 30)
    {
        return preg_match("/^[\x{4e00}-\x{9fa5}]{1,{$maxlength}}$/iu", $str);
    }
    
    /*
     * 是否汉字、英文字母或数字
     */
    public function isNormalWord($str, $maxlength = 30)
    {
        return preg_match("/^[\x{4e00}-\x{9fa5}a-z0-9]{1,{$maxlength}}$/iu", $str);
    }
}
