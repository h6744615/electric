<?php
namespace Windward\Core;

use Windward\Extend\Text;
use Windward\Mvc\Controller;

class Router extends Base {
    
    private $controllerSuffix = 'Controller';
    private $actionSuffix = 'Action';

    private $routes = array();
    private $namedRoutes = array();

    private $activeRoute = null;

    private $defaultController = 'index';
    private $defaultAction = 'index';

    private $notFoundHandler = null;

    public function __construct(Container $container)
    {
        parent::__construct($container);
    }

    public function addRoute($method, $pattern, $handler = null, $name = '')
    {
        $methods = array();
        switch ($method) {
            case Http::METHOD_ANY:
                $methods = array(
                    Http::METHOD_GET,
                    Http::METHOD_POST,
                );
                break;
            default:
                $methods[] = $method;
                break;
        }
        if ($name) {
            $this->namedRoutes[$name] = $pattern;
        }
        $route = new Route($pattern, $handler);
        foreach ($methods as $method) {
            $this->routes[$method][$pattern] = $route;
        }
        return $route;
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
        $this->addRoute(Http::METHOD_ANY, '/:controller/:action/:param_pairs');
        $request = Request::build($this->container);
        $method = $request->getMethod();
        $controllerName = $this->defaultController;
        $actionName = $this->defaultAction;

        if (!isset($this->routes[$method])) {
            goto end_loop;
        }
        $activeRoutes = $this->routes[$method];

        foreach ($activeRoutes as $route) {
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
        
        $request->setNormalizedUri($controllerName . '/' . $actionName);
        $this->container->request = $request;

        $controller = $this->container->controller($controllerName);
        $actionName .= $this->actionSuffix;

        end_loop:
        if (isset($controller) && $actionName && is_callable(array($controller, $actionName))) {
            $response = call_user_func(array($controller, $actionName));
            return $response->output();
        }
        return call_user_func($this->getNotfoundHandler());
    }

    public function getParams()
    {
        if (is_null($this->activeRoute)) {
            return null;
        }
        return $this->activeRoute->getParams();
    }

    public function getNotFoundHandler()
    {   
        if ($this->notFoundHandler) {
            return $this->notFoundHandler;
        }
        $controller = new Controller($this->container);
        return array($controller, 'error404' . $this->actionSuffix);
    }

    public function setNotFoundHandler(array $handler)
    {   
        $controller = $this->container->controller($handler[0]);
        $actionName = $handler[1] . $this->actionSuffix;
        if ($controller && $actionName && is_callable(array($controller, $actionName))) {
            $this->notFoundHandler = array($controller, $actionName);
        }
    }
}