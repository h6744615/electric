<?php
  
namespace Windward\Core;

class Router extends Base {
    
    private $controllerSuffix = 'Controller';
    private $actionSuffix = 'Action';

    private $routes = array();
    private $activeRoute = null;

    private $defaultController = 'index';
    private $defaultAction = 'index';

    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $route = new Route('/:controller/:action/:param_pairs');
        $this->routes[] = $route;
    }

    public function setControllerSuffix($suffix)
    {
        $this->controllerSuffix = $suffix;
    }

    public function getControllerSuffix()
    {
        return $this->controllerSuffix;
    }

    public function setActionSuffix($suffix)
    {
        $this->actionSuffix = $suffix;
    }

    public function getActionSuffix()
    {
        return $this->actionSuffix;
    }

    public function handle($uri)
    {
        foreach ($this->routes as $route) {
            if ($route->test($uri)) {
                $this->activeRoute = $route;
                break;
            }
        }
        if (is_null($this->activeRoute)) {
            $controllerName = $this->defaultController;
            $actionName = $this->defaultAction . $this->actionSuffix;
        } else {
            $controllerName = $this->activeRoute->getControllerName();
            $actionName = $this->activeRoute->getActionName() . $this->actionSuffix;
        }
        $controller = $this->container->controller($controllerName);
        $response = call_user_func(array($controller, $actionName));
        return $response;
    }

    public function getParams()
    {
        if (is_null($this->activeRoute)) {
            return null;
        }
        return $this->activeRoute->getParams();
    }
}