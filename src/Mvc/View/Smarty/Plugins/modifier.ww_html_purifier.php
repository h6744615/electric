<?php

function smarty_modifier_ww_html_purifier($html)
{
    static $purifier = false;
    if (!$purifier) {
        $purifier = new HTMLPurifier();
    }
    return $purifier->purify($html);
}
