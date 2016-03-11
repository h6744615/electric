<?php

function smarty_function_ww_html_checkbox($params, &$smarty) {
    $result = '';

    $id = $params['id'];
    $class = trim($params['class']);
    $sep = $params['sep'];
    $bit = $params['bit'];

    if (empty($params['code'])) {
        return $result;
    }

    if ($bit) {
        $params['value'] = is_array($params['value']) ? (int) array_sum($params['value']) : (int) $params['value'];
    } elseif ((!is_array($params['value']) && strlen($params['value']) == 0) || (is_array($params['value']) && empty($params['value']))) {
        $params['value'] = $params['default'] ? explode(",", $params['default']) : $params['default'];
    }

    foreach ($params['code'] as $k => $v) {

        $checked = '';
        if ($bit) {
            $checked = $v['value'] & $params['value'] ? 'checked="checked"' : '';
        } elseif (in_array($v['value'], (array) $params['value'])) {
            $checked = 'checked="checked"';
        }

        $curid = $id ? "{$id}_{$v['value']}" : "{$params['name']}_{$v['value']}";
        $curclass = $class ? ' ' . $class : '';

        $chexkbox = '<input type="checkbox" id="' . $curid . '" class="checkbox' . $curclass . '" name="' . $params['name'] . '[]" value="' . $v['value'] . '" ' . $checked . ' /> ';

        if (isset($params['label'])) {
            $chexkbox = '<label for="' . $curid . '">' . $chexkbox . $v['name'] . '</label>';
        } else {
            $chexkbox .= $v['name'] . ' ';
        }

        if (isset($params['tag'])) {
            $result.= "<{$params['tag']}>{$chexkbox}</{$params['tag']}>{$sep}";
        } else {
            $result.= $chexkbox . $sep;
        }
    }

    return $result;
}
