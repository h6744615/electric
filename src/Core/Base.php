<?php

/**
*  基类
*  @author Windward
*/
namespace Windward\Core;

class Base {
    
    protected $container = null;
    
    public function __construct(Container $container)
    {
        $this->container = $container;
    }
    
    public function __get($name)
    {
        return $this->container->{$name};
    }
}