<?php

namespace Windward\Core;

abstract class Response extends Base
{

    const CONTENT_TYPE_HTML = "text/html;charset=utf-8";
    const CONTENT_TYPE_JSON = "application/json;charset=utf-8";

    abstract public function output($return = false);
}
