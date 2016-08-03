<?php

function formatDateDiff($start, $end = null)
{
    if (!($start instanceof DateTime)) {
        $start = new DateTime($start);
    }
    
    if ($end === null) {
        $end = new DateTime();
    }
    if (!($end instanceof DateTime)) {
        $end = new DateTime($end);
    }
    $interval = $end->diff($start);
    $format = array();
    if ($interval->y !== 0) {
        $format[] = "%Y年";
    }
    if ($interval->m !== 0) {
        $format[] = "%M月";
    }
    if ($interval->d !== 0) {
        $format[] = "%D天";
    }
    if ($interval->h !== 0) {
        $format[] = "%H小时";
    }
    if ($interval->i !== 0) {
        $format[] = "%I分钟";
    }
    if ($interval->s !== 0) {
        if (!count($format)) {
            return "少于一分钟";
        }
    }
    return $interval->format(join($format));
}

function htmlFormatDateDiff($start, $end = null)
{
    if (!($start instanceof DateTime)) {
        $start = new DateTime($start);
    }
    
    if ($end === null) {
        $end = new DateTime();
    }
    if (!($end instanceof DateTime)) {
        $end = new DateTime($end);
    }
    $interval = $end->diff($start);
    $format = array();
    if ($interval->y !== 0) {
        $format[] = "%Y<span>年</span>";
    }
    if ($interval->m !== 0) {
        $format[] = "%M<span>月</span>";
    }
    if ($interval->d !== 0) {
        $format[] = "%D<span>天</span>";
    }
    if ($interval->h !== 0) {
        $format[] = "%H<span>小时</span>";
    }
    if ($interval->i !== 0) {
        $format[] = "%I<span>分钟</span>";
    }
    if ($interval->s !== 0) {
        if (!count($format)) {
            return "<span>少于一分钟</span>";
        }
    }
    return $interval->format(join($format));
}

function HTMLPurifier($html, $config = null)
{
    static $purifier = false;
    if (!$purifier) {
        $purifier = new HTMLPurifier();
    }
    return $purifier->purify($html, $config);
}
