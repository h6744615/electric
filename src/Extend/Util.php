<?php

namespace Windward\Extend;

class Util extends \Windward\Core\Base
{

    /**
     *  [数组方法] 按某列的值分组
     *
     *  @param array $data 需要被分组的内容
     *  @param string $key 自定义KEY，默认为id
     *  @return array $data 分组处理后的内容
     */
    public static function groupArray(array $data, $key = 'id')
    {
        if (is_null($key)) {
            return null;
        }
        $return = array();
        foreach ($data as $value) {
            $return[$value[$key]][] = $value;
        }
        return $return;
    }

    /**
     * 按某列的值去重,后面的值会覆盖前面的值
     *
     * @param array $data
     * @param string $key
     * @param string|array $fields 需要保留的列
     * @return array
     */
    public static function uniqueArray(array $data, $key = 'id', $fields = null)
    {
        if (is_null($key)) {
            return null;
        }
        if (is_array($fields)) {
            $fields = array_flip($fields);
        }
        $return = array();
        foreach ($data as $value) {
            if (is_null($fields)) {
                $return[$value[$key]] = $value;
            } else if (is_array($fields)) {
                $return[$value[$key]] = array_intersect_key($fields, $value);
            } else {
                $return[$value[$key]] = $value[$fields];
            }
        }
        return $return;
    }

    /**
     * 判断数组中有没有指定的键
     *
     * @param array $data
     * @param string $key 传入 . 分隔的key时判断嵌套数组
     * @return boolean
     */
    public static function issetArrayValue(array $data, $key)
    {
        $ptr = $data;
        foreach (explode('.', $key) as $part) {
            if (!isset($ptr[$part])) {
                return false;
            }
            $ptr = $ptr[$part];
        }
        return isset($ptr);
    }

    /**
     * 获得数组中指定的值
     *
     * @param array $data
     * @param string $key 传入 . 分隔的key是获得嵌套数组中的值
     * @return boolean
     */
    public static function getArrayValue($data, $key)
    {
        $ptr = $data;
        foreach (explode('.', $key) as $part) {
            $ptr = $ptr[$part];
        }
        return $ptr;
    }

    /**
     * 设置数组中指定的值
     *
     * @param array $data
     * @param string $key 传入 . 分隔的key是设置嵌套数组中的值
     * @return boolean
     */
    public static function setArrayValue(&$data, $key, $value)
    {
        $ptr = &$data;
        foreach (explode('.', $key) as $part) {
            if (!isset($ptr[$part])) {
                $ptr[$part] = array();
            }
            $ptr = &$ptr[$part];
        }
        $ptr = $value;
        return true;
    }

    public static function delArrayValue($data, $key)
    {
        return false;
        return true;
    }

    /**
     * 将字段值转换成string
     *
     * @param array|object $data
     * @return array|object
     */
    public static function stringValues(&$data)
    {
        if (is_array($data)) {
            foreach ($data as &$value) {
                if (!is_array($value) && !is_object($value)) {
                    $value = (string) $value;
                } else {
                    self::stringValues($value);
                }
            }
        } else if (is_object($data)) {
            $array = get_object_vars($data);
            self::stringValues($array);
            foreach ($array as $key => $value) {
                $data->{$key} = $value;
            }
        }
    }

    /**
     * 格式化时间
     *
     * 当天的返回时间
     * 七天内返回天
     * 一月内返回周
     * 返回月
     *
     * @param int|string $time 时间
     * @return string
     */
    public static function formatTimeAgo($time)
    {
        if ($time == '0000-00-00 00:00:00' || $time == '0000-00-00') {
            return '';
        } elseif (!is_numeric($time)) {
            $time = strtotime($time);
        }
        $now = time();
        $date = date('Y-m-d', $time);
        $today = date('Y-m-d', $now);
        if ($date == $today) {
            return date('Y-n-j', $time);
        }
        $diff = date_diff(new \DateTime($date), new \DateTime($today));
        if ($diff->days < 30) {
            return $diff->days . '天前';
        }
        return round($diff->days / 30.0) . '月前';
    }
    
    public static function getThumbUrl($fileUrl,$w = 100, $h = 100) {
        if (!preg_match('/\.(png|jpeg|jpg|gif)$/i', $fileUrl)) {
            return $fileUrl;
        }
        
        if (preg_match('/\.(png|jpeg|jpg|gif)$/i', $fileUrl,$match) && !preg_match('/\_w[\d]+h[\d]+/i',$fileUrl)) {
            $ext = $match[1];
            return $fileUrl . "_w{$w}h{$h}.{$ext}";
        }
        return $fileUrl;
    }
}
