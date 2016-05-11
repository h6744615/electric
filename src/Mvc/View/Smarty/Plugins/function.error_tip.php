<?php

function smarty_function_error_tip($params, &$smarty) {
    $result = '';

    $class = isset($params['class']) ? $params['class'] : 'error';
    $errors = $params['errors'];
    $tmp = preg_split('/,/', $params['name'], -1, PREG_SPLIT_NO_EMPTY);
    $tag = isset($params['tag']) ? $params['tag'] : '<div class="tags">&nbsp;&nbsp;&nbsp;<img class="label" src="/common/img/iconfont-tishi-2.png" alt=""/><div class="label-block"><div class="label-text">$message</div><div class="point"></div></div></div>';
    if (empty($errors) || !$tmp) {
        return $result;
    }

    foreach ($tmp as $key) {
        if ($errors[$key]) {
            $result .= isset($params['tag']) ? ('<' . $tag . ' class="' . $class . '"' . '>' . $errors[$key] . '</' . $tag . '>') : str_replace('$message', $errors[$key], $tag);
            break;
        }
    }

    return $result;
}