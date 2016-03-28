<?php

namespace Windward\Core;

use Windward\Extend\Util;

class Request
{

    private $post = array();
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
            $contenttype = strtolower($_SERVER['CONTENT_TYPE']);
            if (preg_match('/^application\/json/',$contenttype)) {
                $this->post = (array)json_decode(file_get_contents('php://input'), true);
            } else {
                $this->post = $_POST;
            }
        }
    }

    public function getQuery($name = null, $default = null)
    {
        if (is_null($name)) {
            return $_GET;
        }
        if (Util::issetArrayValue($_GET, $name)) {
            return Util::getArrayValue($_GET, $name);
        }
        return $default;
    }

    public function getPost($name = null, $default = null)
    {
        if (is_null($name)) {
            return $this->post;
        }
        if (Util::issetArrayValue($this->post, $name)) {
            return Util::getArrayValue($this->post, $name);
        }
        return $default;
    }

    public function getFile()
    {
        
    }

    public function getCookie()
    {
        
    }

    public function setCookie()
    {
        
    }

    public function getServer($key)
    {
        return filter_input(INPUT_SERVER, $key, FILTER_SANITIZE_STRING);
    }

    public function isGet()
    {
        return $this->getServer('REQUEST_METHOD') == 'GET';
    }

    public function isPost()
    {
        return $this->getServer('REQUEST_METHOD') == 'POST';
    }
    
    public function isComplete() {
        return $this->getPost('complete') && $this->isPost();
    }

    public function getNormalizedUri()
    {
        return $this->normalizedUri;
    }

    public function setNormalizedUri($uri)
    {
        $this->normalizedUri = $uri;
    }

    public function hasFile()
    {
        
    }

    public function hasCookie()
    {
        
    }

    public function getMethod()
    {
        switch ($this->getServer('REQUEST_METHOD')) {
            case 'GET':
                return Http::METHOD_GET;
                break;

            case 'POST':
                return Http::METHOD_POST;
                break;

            default:
                # code...
                break;
        }
    }

    public function getSchemaHost()
    {
        return $this->isHttps() ? 'https' : 'http' . '://' . $this->getServer('HTTP_HOST');
    }

    function isHttps()
    {
        if ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) {
            return true;
        } 
        return false;
    }
    
    public function getIp() {
        $ip = '';
        if (getenv('HTTP_CLIENT_IP')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } else if (getenv('HTTP_X_FORWARDED_FOR')) {
            $ip = getenv('HTTP_X_FORWARDED_FOR');
        } else if (getenv('REMOTE_ADDR')) {
            $ip = getenv('REMOTE_ADDR');
        }
        return $ip;
    }

}
