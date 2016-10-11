<?php

use Windward\Core\Router;
use Windward\Core\Http;

$router = new Router($container);
$router->addRoute(Http::METHOD_GET, '/demo/custom/uri', array('Demo', 'customuri'), 'merchant_customuri');
$router->setNotfoundHandler(array('base', 'error404'));

return $router;
