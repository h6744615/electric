<?php

namespace Windward\Extend;

class Validator extends \Windward\Core\Base {

    public function isMobile($data) {
        $regex = '/^1[0-9]{10}$/';
        return preg_match($regex, $data);
    }
    
    public function isNumber($data) {
        $regex = '/^[0-9]+$/';
        return preg_match($regex, $data);
    }

    public function isMail($data) {
        $regex = "/^[a-zA-Z0-9_\-\.]+@[a-zA-Z0-9\._\-]*\.[a-zA-Z0-9\._\-]*$/is";
        return preg_match($regex, $data);
    }

    public function isNumeric($data) {
        $regex = '/^[1-9]+[0-9]*$/';
        return preg_match($regex, $data);
    }

    public function isFloat($data) {
        $regex = '/^([1-9]+[0-9]*$)|([0-9]{1}\.[0-9]+$)|([1-9]+[0-9]*\.[0-9]+)$/';
        return preg_match($regex, $data);
    }

    public function isDate($data) {
        $regex = "/^(19|20)[0-9]{2}\-[0-1]{1}[0-9]{1}\-[0-3]{1}[0-9]{1}$/";

        if (!preg_match($regex, $data)) {
            return false;
        }

        $time = strtotime($data);
        return (date('Y-m-d', $time) !== $data) ? false : true;
    }

    public function isDateTime($data) {
        $regex = "/^(19|20)[0-9]{2}\-[0-1]{1}[0-9]{1}\-[0-3]{1}[0-9]{1}\s+[0-2]{1}[0-9]{1}:[0-5]{1}[0-9]{1}:[0-5]{1}[0-9]{1}$/";

        if (!preg_match($regex, $data)) {
            return false;
        }

        $time = strtotime($data);
        return (date('Y-m-d H:i:s', $time) !== $data) ? false : true;
    }
    
    public function isShortDateTime($data) {
        $regex = "/^(19|20)[0-9]{2}\-[0-1]{1}[0-9]{1}\-[0-3]{1}[0-9]{1}\s+[0-2]{1}[0-9]{1}:[0-5]{1}[0-9]{1}$/";

        if (!preg_match($regex, $data)) {
            return false;
        }

        $time = strtotime($data);
        return (date('Y-m-d H:i', $time) !== $data) ? false : true;
    }

    public function isHour($data) {
        $regex = "/^[0-9]{1,2}$/";

        if (!preg_match($regex, $data)) {
            return false;
        } else {
            return ($data >= 0 && $data < 24) ? true : false;
        }
    }

    public function isPassword($data) {
        $regex = '/^[0-9a-z]{6,12}$/';
        return preg_match($regex, $data);
    }

}
