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
        $count = preg_match($this->pattern, $uri, $m);
        if ($count == 0) {
            return false;
        }
        $c = count($m) - 1;
        if ($c > 1 && is_null($this->handler)) {
            $this->controllerName = $m[1];
        } elseif($this->handler) {
            $this->controllerName = $this->handler[0];
        }
        if ($c > 2 && is_null($this->handler)) {
            $this->actionName = $m[2];
        } elseif(count($this->handler) > 1) {
            $this->controllerName = $this->handler[1];
        }
        if ($this->hasParams) {                
            $paramParts = preg_split('#/#', $m[$c], 0, PREG_SPLIT_NO_EMPTY);
            if ($this->paramsPaired) {
                $c = count($paramParts);
                for ($i = 0; $i < $c - 1 ; $i += 2) {
                    $this->params[$paramParts[$i]] = $paramParts[$i+1];
                }
            } else {
                $this->params = $paramParts;
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