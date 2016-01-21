<?php
namespace Windward\Mvc;

use Windward\Core\Container;

Class Controller extends \Windward\Core\Base {
    
    protected $container;

    public function __construct(Container $container)
    {

        $this->container = $container;   
    }


}