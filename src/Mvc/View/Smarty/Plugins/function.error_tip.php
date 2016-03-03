<?php

function smarty_function_error_tip($params, &$smarty)
{
    $result = '';

    $class = isset($params['class']) ? $params['class'] : 'error';
    $errors = $params['errors'];
    $tmp = preg_split('/,/', $params['name'], -1, PREG_SPLIT_NO_EMPTY);
    $tag = isset($params['tag']) ? $params['tag'] : 'span';

    if (empty($errors) || !$tmp) {
        return $result;
    }

    foreach ($tmp as $key) {
        if ($errors[$key]) {
            $result .= '<' . $tag . ' class="' . $class . '"' . '>' . $errors[$key] . '</' . $tag . '>';
            break;
        }
    }

    return $result;
}
