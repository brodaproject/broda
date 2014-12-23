<?php

namespace Broda\Core\Provider\Sensio\FrameworkExtra;

use Broda\Core\Container\RouteProviderInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Routing\AnnotatedRouteControllerLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
use Symfony\Component\Routing\RouteCollection;

class RoutingExtraProvider implements ServiceProviderInterface, RouteProviderInterface
{

    public function register(Container $c)
    {
        // TODO: será que não seria melhor se, no FileLocator fosse passado o root
        //       do projeto e no load() (no método route abaixo) fosse passado
        //       o caminho do diretório dos controllers de forma relativa?
        $c['extra.route_loader'] = function ($c) {
            return new AnnotationDirectoryLoader(
                new FileLocator($c['extra.controllers_dir']),
                new AnnotatedRouteControllerLoader($c['annotation.reader'])
            );
        };
    }

    public function route(Container $c, RouteCollection $routes)
    {
        if (isset($c['extra.controllers_dir'])) {
            $routes->addCollection($c['extra.route_loader']->load('', 'annotation'));
        }
    }
} 