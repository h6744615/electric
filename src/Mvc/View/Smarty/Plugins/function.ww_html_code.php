<?php

function smarty_function_ww_html_code($params, &$smarty)
{
    $result = '';
    if (empty($params['code'])) {
        return $result;
    }

    $value = $params['value'];

    foreach ($params['code'] as $v) {
        if ($value == $v['value']) {
            $result = $v['name'];
            break;
        }
    }

    return $result;
}
