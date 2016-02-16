<?php
namespace Windward\Mvc;

use Windward\Core\Container;
use Windward\Core\Language;

Class Controller extends \Windward\Core\Base {
    
    protected $container;
    private $language = null;
    
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function getLanguage()
    {
        if (!is_null($this->language)) {
            return $this->language;
        }
        return $this->language = new Language('controller');
    }

    public function error404Action()
    {
        $this->view->display('404');
    }
}