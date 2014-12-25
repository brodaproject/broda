<?php

namespace Broda\Core\Provider\Sensio\FrameworkExtra\Routing;

use Psr\Log\LoggerInterface;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Router as BaseRouter;

class Router extends BaseRouter
{
    protected $appendCollection;

    public function __construct(RouteCollection $routes, LoaderInterface $loader, $resource, array $options = array(), RequestContext $context = null, LoggerInterface $logger = null)
    {
        parent::__construct($loader, $resource, $options, $context, $logger);
        $this->appendCollection = $routes;
    }

    public function getRouteCollection()
    {
        $collection = parent::getRouteCollection();

        if (null !== $this->appendCollection) {
            $collection->addCollection($this->appendCollection);
            $this->appendCollection = null;
        }

        return $collection;
    }

} 