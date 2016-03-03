<?php

function smarty_function_array_to_hidden($params, &$smarty)
{
    $array = $params['value'];
    return array_to_hidden($array);
}

function array_to_hidden($array, $parentKey = '')
{
    if (!$array) {
        return '';
    }
    $str = '';
    foreach ($array as $key => $val) {
        if (is_array($val)) {
            $key = $parentKey ? $parentKey . '[' . $key . ']' : $key;
            $str .= array_to_hidden($val, $key);
        } else if ($parentKey) {
            $str .= '<input type="hidden" name="' . $parentKey . '[' . $key . ']" value="' . $val . '" />' . "\r\n";
        } else {
            $str .= '<input type="hidden" name="' . $key . '" value="' . $val . '" />' . "\r\n";
        }
    }
    return $str;
}

?>
