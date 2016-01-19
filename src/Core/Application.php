<?php

/**
*  基类
*  @author Windward
*/
namespace Windward\Core;

class Application extends Base {
    
    private $container = null;

    public function handle()
    {
        echo "Hello World";
    }

}