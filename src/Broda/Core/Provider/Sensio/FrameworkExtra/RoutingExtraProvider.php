<?php

namespace Broda\Core\Provider\Sensio\FrameworkExtra;

use Broda\Core\Provider\Sensio\FrameworkExtra\Routing\Router;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Routing\AnnotatedRouteControllerLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;

class RoutingExtraProvider implements ServiceProviderInterface
{

    public function register(Container $c)
    {
        // TODO dar opção de mudar o loader, não só annotations
        $c['extra.options'] = array(
            'root_dir' => null,
            'controller_namespace' => null,
            'cache_dir' => sys_get_temp_dir(),
        );

        $c['extra.route_loader'] = function ($c) {
            return new AnnotationDirectoryLoader(
                new FileLocator($c['extra.options']['root_dir']),
                new AnnotatedRouteControllerLoader($c['annotation.reader'])
            );
        };

        $c['extra.router'] = function ($c) {
            $router = new Router(
                $c['routes'],
                $c['extra.route_loader'],
                $c['extra.options']['controller_namespace'],
                array(
                    'cache_dir'             => $c['extra.options']['cache_dir'],
                    'debug'                 => $c['debug'],
                    'generator_dumper_class'=> 'Symfony\Component\Routing\Generator\Dumper\PhpGeneratorDumper',
                    'generator_cache_class' => 'BrodaUrlGenerator',
                    'matcher_dumper_class'  => 'Symfony\Component\Routing\Matcher\Dumper\PhpMatcherDumper',
                    'matcher_cache_class'   => 'BrodaRequestMatcher',
                    'resource_type' => 'annotation',
                ),
                $c['routing.request_context']
            );
            return $router;
        };

        $c->extend('routing.url_generator', function ($generator, $c) {
            /* @var $router Router */
            $router = $c['extra.router'];
            $router->setOption('generator_class', get_class($generator));
            $router->setOption('generator_base_class', get_class($generator));
            return $router;
        });

        $c->extend('routing.request_matcher', function ($matcher, $c) {
            /* @var $router Router */
            $router = $c['extra.router'];
            $router->setOption('matcher_class', get_class($matcher));
            $router->setOption('matcher_base_class', get_class($matcher));
            return $router;
        });

    }

} 