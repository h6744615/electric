<?php

namespace Windward\Core;

class Container
{

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

    /**
     * 注册依赖
     *
     * @param string $name
     * @param mixed|\Closure $value
     */
    public function set($name, $value)
    {
        if ($value instanceof \Closure) {
            $this->items[$name] = $value();
        } else {
            $this->items[$name] = $value;
        }
    }

    public function get($name)
    {
        return $this->items[$name];
    }

    public function __get($name)
    {
        if (isset($this->items[$name])) {
            return $this->items[$name];
        }
        return null;
    }

    /**
     * 通过名称生成Controller对象并缓存
     *
     * @param string $name
     * @return \Windward\Core\Controller
     */
    public function controller($name)
    {
        $name = $this->controllerNamespace . '\\' . ucfirst($name);
        if (isset($this->items[$name])) {
            return $this->items[$name];
        }
        if (!class_exists($name)) {
            return null;
        }
        $controller = new $name($this);
        $this->items[$name] = $controller;
        return $controller;
    }

    /**
     * 通过名称生成Controller对象并缓存
     *
     * @param string $name  model名称
     * @param string $connectionName  连接名称
     * @return \Windward\Core\Controller
     */
    public function model($name, $connectionName = 'connection')
    {
        $name = $this->modelNamespace . '\\' . $name;
        if (isset($this->items[$name])) {
            return $this->items[$name];
        }
        if (!class_exists($name)) {
            return null;
        }
        $model = new $name($this);
        //$model->setDbConnection($this->database);
        $connection = $this->{$connectionName};
        $model->setPdo($connection);
        $this->items[$name] = $model;
        return $model;
    }
}
