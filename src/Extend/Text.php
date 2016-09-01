<?php

namespace Windward\Extend;

class Text extends \Windward\Core\Base
{

    public static function camelCase($str)
    {
        if (strpos($str, '_') === false) {
            return $str;
        }
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $str)));
    }

    public static function underLine($str)
    {
        $camelCase = preg_replace_callback("/([A-Z]{1})/", function($match){
            return "_" . strtolower($match[1]);
        }, $str);
        
        return $camelCase;
    }

}
