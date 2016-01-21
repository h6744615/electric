<?php
namespace Windward\Core;

use Windward\Extend\Text;

class Container extends Base {
    
    private $items;
    private $controllerSuffix = 'Controller';
    private $controllerNamespace = '';
    private $modelNamespace = '';

    public function setControllerNamespace($namespace)
    {
        $this->controllerNamespace = rtrim($namespace, '\\');
    }

    public function getControllerNamespace()
    {
        return $this->controllerNamespace;
    }

    public function setModelNamespace($namespace)
    {
        $this->modelNamespace = $namespace;
    }

    public function getModelNamespace()
    {
        return $this->modelNamespace;
    }

    public function set($name, $value)
    {
        $this->items[$name] = $value;
    }

    public function get($name)
    {
        return $this->items[$name];
    }

    public function __get($name)
    {
        return $this->items[$name];
    }

    public function controller($name)
    {
        $name = $this->controllerNamespace . '\\' . ucfirst(Text::camelCase($name));
        if (isset($this->items[$name])) {
            return $this->items[$name];
        }
        $controller = new $name($this);
        $this->items[$name] = $controller;
        return $controller;
    }
}