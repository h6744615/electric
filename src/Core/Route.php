<?php
  
namespace Windward\Core;

class Route extends Base {

    private $pattern;
    private $handler;
    private $idPattern = '([a-zA-Z0-9_]+)';

    private $controllerName;
    private $actionName;

    private $params = array();
    private $hasParams = false;
    private $paramsPaired = false;

    public function __construct($pattern, $handler = null)
    {
        if (strpos($pattern, ':params') !== false) {
            $this->hasParams = true;
        } elseif (strpos($pattern, ':param_pairs') !== false) {
            $this->hasParams = true;
            $this->paramsPaired = true;
        }
        $pattern = str_replace(array(
            ':controller',
            ':action',
            '/:params',
            '/:param_pairs',
        ), array(
            $this->idPattern, 
            $this->idPattern, 
            '(.*)', 
            '(.*)'
        ), $pattern);
        $this->pattern = '~^' . $pattern . '~';
        $this->handler = $handler;
    }

    public function test($uri)
    {
        $pattern = str_replace('/', '//', $this->pattern);
        $regexp = preg_replace_callback('#(?:\[([\w]+):([^]]+)\])|(?::(\w+))#', function($m) {
            if ($m[1]) {
                return '(?P<'. preg_quote($m[1]) . '>' . $m[2] . ')';
            }
            return '(?P<'. preg_quote($m[3]) . '>[^/]*)';
        }, $pattern);
        $regexp = str_replace('//', '/', $regexp);
        $count = preg_match($regexp, $uri, $m);
        if ($count == 0) {
            return false;
        }
        if ($this->handler) {
            $this->controllerName = $this->handler[0];
            $this->actionName = $this->handler[1];
            $this->params = $m;
        } else {
            array_shift($m);
            $this->controllerName = array_shift($m);
            $this->actionName = array_shift($m);
            if ($this->hasParams && $this->paramsPaired) {
                $parts = explode('/', $m[0]);
                $count = count($parts);
                if ($count % 2 != 1) {
                    return false;
                }
                for ($i = 1; $i < $count - 1; $i += 2) {
                    $this->params[$parts[$i]] = $parts[$i + 1];
                }
            }
        }
        return true;
    }

    public function getControllerName()
    {
        return $this->controllerName;   
    }

    public function getActionName()
    {
        return $this->actionName;
    }

    public function getParams()
    {
        return $this->params;
    }
}