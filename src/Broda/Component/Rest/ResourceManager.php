<?php

namespace Broda\Component\Rest;

use Pimple\Container;
use Silex\ControllerCollection;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Classe ResourceManager
 *
 * @author raphael
 */
class ResourceManager
{
    /**
     *
     * @var Container
     */
    private $container;

    /**
     *
     * @var RouteCollection|ControllerCollection
     */
    private $routeCollection;

    /**
     *
     * @var Resource[]
     */
    private $resources;

    private $routeClass;

    public function __construct($controllers, Container $container = null, $routeClass = 'Symfony\Component\Routing\Route')
    {
        $this->container = $container ?: new Container();
        $this->routeCollection = $controllers;
        $this->routeClass = $routeClass;
        $this->resources = array();
    }

    public function resource($path, $controller = null)
    {
        if (!isset($this->resources[$path])) {
            $this->resources[$path] = new Resource($this, $path, $controller);
        }
        return $this->resources[$path];
    }

    public function getContainer()
    {
        return $this->container;
    }

    public function setContainer(Container $container)
    {
        $this->container = $container;
        return $this;
    }

    public function match(Resource $resource, $routeType, $to)
    {
        if ($this->routeCollection instanceof ControllerCollection) {
            // Silex style
            $controller = $this->routeCollection->match($resource->getPath($routeType), $to)
                    ->method(implode('|', $resource->getMethods($routeType)))
                    ->bind($resource->getName($routeType));

            return $controller->getRoute();

        } else {
            // Symfony standard style
            $routeClass = $this->routeClass;
            /* @var $route Route */
            $route = new $routeClass();
            $route->setPath($resource->getPath($routeType));
            $route->setDefault('_controller', $to);
            $route->setMethods($resource->getMethods($routeType));

            $this->routeCollection->add($resource->getName($routeType), $route);

            return $route;
        }
    }
}
