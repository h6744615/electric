<?php

/**
 * 应用类
 */
namespace Windward\Core;

class Application extends Base {
    
    /**
     * 处理uri
     * 
     * @param string $uri
     */
    public function handle($uri = null)
    {
        if (is_null($uri)) {
            $uri = $_SERVER['PATH_INFO'];
            $this->container->router->handle($uri);
        }
    }

}