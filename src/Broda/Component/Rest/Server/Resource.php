<?php

namespace Broda\Component\Rest\Server;

use Symfony\Component\Routing\Route;

/**
 *
 * @author Raphael Hardt <raphael.hardt@gmail.com>
 */
class Resource
{

    /**
     *
     * @var ResourceManager
     */
    private $rm;

    private $basePath;

    private $idName;

    private $format;

    /**
     *
     * @var Route[]
     */
    protected $routes = array();

    public static $defaultMethods = array(
        'all' => 'all',
        'post' => 'post',
        'get' => 'get',
        'put' => 'put',
        'patch' => 'patch',
        'delete' => 'delete',
    );

    public function __construct(ResourceManager $rm, $path, $controller = null)
    {
        $controller = $this->createServiceForController($controller);

        $this->rm = $rm;
        $this->constructPath(ltrim($path, '/'));

        if (null !== $controller) {
            foreach (self::$defaultMethods as $routeName => $method) {
                $this->rm->match($this, $routeName, sprintf('%s:%s', $controller, $method));
            }
        }
    }

    private function constructPath($path)
    {
        $matches = array();
        if (!preg_match('#(.+?)/\{([^}]+)\}(\..+)?$#', $path, $matches)) {
            throw new \LogicException('Path must be in a format: /rest/{id}.format');
        }

        $this->idName = $matches[2];
        $this->basePath = '/'.$matches[1];
        $this->format = $matches[3];
    }

    public function getPath($routeType)
    {
        if ($this->isItemPath($routeType)) {
            return $this->getPathForSubresource() . $this->format;
        } else {
            return $this->basePath . $this->format;
        }
    }

    protected function getPathForSubresource()
    {
        return $this->basePath . '/{' . $this->idName . '}';
    }

    public function getName($routeType)
    {
        return str_replace('/', '_', $this->basePath . $this->format) . '_' . $routeType;
    }

    public function getMethods($routeType)
    {
        switch (strtolower($routeType)) {
            case 'get':
            case 'all':
                return array('GET');
            default:
                // POST, DELETE, PUT, PATCH
                return array(strtoupper($routeType));
        }
    }

    public function subresource($path, $controller = null)
    {
        $path = '/'.ltrim($path, '/');
        return $this->rm->resource($this->getPathForSubresource() . $path, $controller);
    }

    public function match($routeType, $controller)
    {
        if (isset($this->routes[$routeType])) {
            throw new \LogicException(sprintf('%s route is already set for rest path %s', $routeType, $this->getPath($routeType)));
        }
        return $this->routes[$routeType] = $this->rm->match($this, $routeType, $controller);
    }

    public function all($controller)
    {
        return $this->match('all', $controller);
    }

    public function post($controller)
    {
        return $this->match('post', $controller);
    }

    public function get($controller)
    {
        return $this->match('get', $controller);
    }

    public function put($controller)
    {
        return $this->match('put', $controller);
    }

    public function patch($controller)
    {
        return $this->match('patch', $controller);
    }

    public function delete($controller)
    {
        return $this->match('delete', $controller);
    }

    public function before($closure)
    {
        foreach ($this->routes as $route) {
            if ($route instanceof \Silex\Route) {
                // only works with Silex
                $route->before($closure);
            }
        }
        return $this;
    }

    public function after($closure)
    {
        foreach ($this->routes as $route) {
            if ($route instanceof \Silex\Route) {
                // only works with Silex
                $route->after($closure);
            }
        }
        return $this;
    }

    public function assertId($constraint)
    {
        foreach ($this->routes as $routeType => $route) {
            if ($this->isItemPath($routeType)) {
                $route->setRequirement($this->idName, $constraint);
            }
        }

        return $this;
    }

    public function convert($variable, $callback)
    {
        foreach ($this->routes as $routeType => $route) {
            if ($route instanceof \Silex\Route && $this->isItemPath($routeType)) {
                // only works with Silex
                $route->convert($variable, $callback);
            }
        }

        return $this;
    }

    private function isItemPath($routeType)
    {
        switch (strtolower($routeType)) {
            case 'get':
            case 'put':
            case 'patch':
            case 'delete':
                return true;
        }
        return false;
    }

    private function createServiceForController($controller)
    {
        if (is_object($controller) || class_exists($controller, false)) {
            $ctrlServiceName = $this->classServiceName($controller);

            // cria um serviço temporario para a classe, já que o resource
            // só suporta controllers-serviços
            $container = $this->rm->getContainer();
            $container[$ctrlServiceName] = function () use ($controller) {
                return is_string($controller) ? new $controller : $controller;
            };

            $controller = $ctrlServiceName;
        }
        return $controller;
    }

    private function classServiceName($className)
    {
        if (is_object($className)) {
            $className = get_class($className);
        }
        $className = preg_replace('/([^A-Z])([A-Z])/', "$1_$2", $className);
        $className = str_replace('\\', '.', $className);
        $className = strtolower($className);

        return $className;
    }

}
