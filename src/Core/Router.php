<?php
namespace Windward\Core;

use Windward\Extend\Text;

class Router extends Base {
    
    private $controllerSuffix = 'Controller';
    private $actionSuffix = 'Action';

    private $routes = array();
    private $activeRoute = null;

    private $defaultController = 'index';
    private $defaultAction = 'index';

    public function __construct(Container $container)
    {
        parent::__construct($container);
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
            $actionName = $this->defaultAction;
        } else {
            $controllerName = ucfirst(Text::camelCase($this->activeRoute->getControllerName()));
            $actionName = lcfirst(Text::camelCase($this->activeRoute->getActionName()));
        }
        
        $request = Request::build($this->container);
        $request->setNormalizedUri($controllerName . '/' . $actionName);
        $this->container->request = $request;

        $controller = $this->container->controller($controllerName);
        $actionName .= $this->actionSuffix;
        if (is_callable(array($controller, $actionName))) {
            $response = call_user_func(array($controller, $actionName));
            return $response->output();
        }
        return call_user_func(array($controller, 'error404' . $this->actionSuffix));
    }

    public function getParams()
    {
        if (is_null($this->activeRoute)) {
            return null;
        }
        return $this->activeRoute->getParams();
    }
}