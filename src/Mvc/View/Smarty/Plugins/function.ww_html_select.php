<?php

function smarty_function_ww_html_select($params, &$smarty) {
    $result = '';

    $group = $params['group'];
    $data = $params['code'];
    $maxSize = $params['maxSize'];

    if ($params['empty'] == 'true') {
        if ($group) {
            $group = array('-1' => '') + $group;
            $data[-1] = array('' => array('name' => '------'));
        } else {
            $data = array('' => array('name' => '------')) + (array) $data;
        }
    }

    if (empty($data)) {
        return $result;
    }

    if (strlen($params['value']) == 0) {
        $params['value'] = $params['default'];
    }

    if ($group) {
        foreach ($group as $gid => $label) {
            $result .= '<optgroup label="' . $label . '">';
            foreach ((array) $data[$gid] as $key => $val) {
                if ($val['value'] == $params['value']) {
                    if (!$maxSize) {
                        $result .= '<option value="' . $val['value'] . '" selected="selected">' . $val['name'] . '</option>';
                    } else {
                        $val_name = $val['name'];
                        if ($val_name != '------') {
                            $len = mb_strlen($val_name, 'UTF-8');
                            if ($len > $maxSize) {
                                $val_name = mb_substr($val_name, 0, $maxSize, 'UTF-8') . '....';
                            } else {
                                $val_name = mb_substr($val_name, 0, $maxSize, 'UTF-8');
                            }
                        }
                        $result .= '<option value="' . $val['value'] . '" selected="selected">' . $val_name . '</option>';
                    }
                } else {
                    if (!$maxSize) {
                        $result .= '<option value="' . $val['value'] . '">' . $val['name'] . '</option>';
                    } else {
                        $val_name = $val['name'];
                        if ($val_name != '------') {
                            $len = mb_strlen($val_name, 'UTF-8');
                            if ($len > $maxSize) {
                                $val_name = mb_substr($val_name, 0, $maxSize, 'UTF-8') . '....';
                            } else {
                                $val_name = mb_substr($val_name, 0, $maxSize, 'UTF-8');
                            }
                        }
                        $result .= '<option value="' . $val['value'] . '">' . $val_name . '</option>';
                    }
                }
            }
            $result .= '</optgroup>';
        }
    } else {
        foreach ((array) $data as $key => $val) {
            if ($val['value'] == $params['value']) {
                if (!$maxSize) {
                    $result .= '<option value="' . $val['value'] . '" selected="selected">' . $val['name'] . '</option>';
                } else {
                    $val_name = $val['name'];
                    if ($val_name != '------') {
                        $len = mb_strlen($val_name, 'UTF-8');
                        if ($len > $maxSize) {
                            $val_name = mb_substr($val_name, 0, $maxSize, 'UTF-8') . '....';
                        } else {
                            $val_name = mb_substr($val_name, 0, $maxSize, 'UTF-8');
                        }
                    }
                    $result .= '<option value="' . $val['value'] . '" selected="selected">' . $val_name . '</option>';
                }
            } else {
                if (!$maxSize) {
                    $result .= '<option value="' . $val['value'] . '">' . $val['name'] . '</option>';
                } else {
                    $val_name = $val['name'];
                    if ($val_name != '------') {
                        $len = mb_strlen($val_name, 'UTF-8');
                        if ($len > $maxSize) {
                            $val_name = mb_substr($val_name, 0, $maxSize, 'UTF-8') . '....';
                        } else {
                            $val_name = mb_substr($val_name, 0, $maxSize, 'UTF-8');
                        }
                    }
                    $result .= '<option value="' . $val['value'] . '">' . $val_name . '</option>';
                }
            }
        }
    }


    return $result;
}
