<?php

namespace Broda\Component\Rest;

use Pimple\Container;
use Silex\ControllerCollection;

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
     * @var ControllerCollection
     */
    private $controllers;

    /**
     *
     * @var Resource[]
     */
    private $resources;

    public function __construct(ControllerCollection $controllers, Container $container = null)
    {
        $this->container = $container ?: new Container();
        $this->controllers = $controllers;
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

    public function getController()
    {
        return $this->controllers;
    }
}
