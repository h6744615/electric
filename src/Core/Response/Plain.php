<?php

namespace Windward\Core\Response;

use Windward\Core\Response;

class Plain extends Response
{
    
    private $contentType = self::CONTENT_TYPE_HTML;
    private $content = "";

    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
        return $this;
    }

    public function setContent($content)
    {
        $this->content = $content;
        return $this;
    }

    public function output($return = false)
    {
        if ($return) {
            return $this->content;
        }
        header("Content-Type: {$this->contentType}");
        echo $this->content;
    }
}
