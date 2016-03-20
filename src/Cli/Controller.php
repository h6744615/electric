<?php
namespace Windward\Cli;

use Windward\Core\Container;
use Windward\Core\Language;

class Controller extends \Windward\Core\Base
{
    
    protected $container;
    private $language = null;
    
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function error404Action()
    {
        echo "NOT FOUND";
    }
}
