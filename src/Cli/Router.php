<?php
namespace Windward\Cli;

use Windward\Core\Base;
use Windward\Core\Container;
use Windward\Core\Route;
use Windward\Extend\Text;

class Router extends Base
{
    
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

    public function addRoute($pattern, $handler = null, $name = '')
    {
        $this->routes[$pattern] = new Route($pattern, $handler);
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
        $this->addRoute(':controller/:action/:param_pairs');
        $controllerName = $this->defaultController;
        $actionName = $this->defaultAction;

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
        
        $controller = $this->container->controller($controllerName);
        $actionName .= $this->actionSuffix;

        end_loop:
        if (isset($controller) && $actionName && is_callable(array($controller, $actionName))) {
            if (is_callable(array($controller, 'beforeHandle'))) {
                call_user_func(array($controller, 'beforeHandle'));
            }
            $response = call_user_func(array($controller, $actionName));
            if (is_callable(array($controller, 'afterHandle'))) {
                call_user_func(array($controller, 'afterHandle'));
            }
            return;
        }
        return call_user_func($this->getNotfoundHandler());
    }

    public function getParams($key = null)
    {
        if (is_null($this->activeRoute)) {
            return null;
        }
        return $this->activeRoute->getParams($key);
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

    public function getActiveRoute()
    {
        return $this->activeRoute;
    }
}
