<?php

function smarty_function_ww_html_radio($params, &$smarty)
{
    $result = '';

    $id = $params['id'];
    $class = trim($params['class']);
    $sep = $params['sep'];
    $bit = $params['bit'];

    if (empty($params['code'])) {
        return $result;
    }

    if (strlen($params['value']) == 0) {
        $params['value'] = $params['default'];
    }

    foreach ($params['code'] as $k => $v) {
        if ($bit) {
            $checked = $v['value'] & $params['value'] ? 'checked="checked"' : '';
        } elseif (strcmp($v['value'], $params['value']) == 0) {
            $checked = 'checked="checked"';
        } else {
            $checked = '';
        }

        $curid = $id ? "{$id}_{$v['value']}" : "{$params['name']}_{$v['value']}";
        $curclass = $class ? ' ' . $class : '';

        $radio = '<input type="radio" id="' . $curid . '" class="radio' . $curclass . '" name="' . $params['name'] . '" value="' . $v['value'] . '" ' . $checked . ' /> ' . $v['name'];

        if (isset($params['label'])) {
            $radio = '<label for="' . $curid . '">' . $radio . ' </label>';
        }

        if (isset($params['tag'])) {
            $result .= "<{$params['tag']}>{$radio}</{$params['tag']}>{$sep}";
        } else {
            $result .= $radio . $sep;
        }
    }

    return $result;
}
