<?php
namespace Windward\Core;

use Windward\Extend\Util;

class Request  {
    
    private $post;
    private $normalizedUri;

    public static function build(Container $container)
    {
        $request = new Request($container);
        $request->init();
        return $request;
    }

    public function init()
    {
        if ($this->isPost()) {
            if (strtolower($_SERVER['CONTENT_TYPE']) == 'application/json') {
                $this->post = json_decode(file_get_contents('php://input'), true);
            } else {
                $this->post = $_POST;
            }
        }
    }

    public function getQuery($name = null, $default = null) {
        if (is_null($name)) {
            return $_GET;
        }
        if (Util::issetArrayValue($_GET, $name)) {
            return Util::getArrayValue($this->post, $name);
        }
        return $default;
    }
    
    public function getPost($name, $default = null) {
        if (is_null($name)) {
            return $this->post;
        }
        if (Util::issetArrayValue($this->post, $name)) {
            return Util::getArrayValue($this->post, $name);
        }
        return $default;
    }
    
    public function getFile() {
        
    }
    
    public function getCookie() {
        
    }
    
    public function setCookie() {
        
    }
    
    public function getServer() {
        
    }
    
    public function isGet() {
        return $_SERVER['REQUEST_METHOD'] == 'GET';
    }
    
    public function isPost() {
        return $_SERVER['REQUEST_METHOD'] == 'POST';
    }
    
    public function getNormalizedUri()
    {
        return $this->normalizedUri;
    }

    public function setNormalizedUri($uri)
    {
        $this->normalizedUri = $uri;
    }

    public function hasFile() {
        
    }
    
    public function hasCookie() {
    
    }

}