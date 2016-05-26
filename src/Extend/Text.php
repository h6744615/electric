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
}
