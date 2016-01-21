<?php

/**
*  基类
*  @author Windward
*/
namespace Windward\Core;

class Application extends Base {
    
    private $container = null;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function handle($uri = null)
    {
        if (is_null($uri)) {
            $uri = $_SERVER['PATH_INFO'];
            $this->container->router->handle($uri);
        }
    }

}