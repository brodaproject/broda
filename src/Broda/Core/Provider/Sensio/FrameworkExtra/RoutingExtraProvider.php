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
        // TODO dar opção de mudar o loader
        $c['extra.options'] = array(
            'root_dir' => null,
            'controller_namespace' => null,
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
                    'cache_dir'             => sys_get_temp_dir(),
                    'debug'                 => $c['debug'],
                    //'generator_class'       => 'Symfony\Component\Routing\Generator\UrlGenerator',
                    'generator_base_class'  => 'Symfony\Component\Routing\Generator\UrlGenerator',
                    'generator_dumper_class'=> 'Symfony\Component\Routing\Generator\Dumper\PhpGeneratorDumper',
                    'generator_cache_class' => 'BrodaUrlGenerator',
                    //'matcher_class'         => 'Broda\Core\Provider\Symfony\Routing\RedirectableUrlMatcher',
                    'matcher_base_class'    => 'Broda\Core\Provider\Symfony\Routing\RedirectableUrlMatcher',
                    'matcher_dumper_class'  => 'Symfony\Component\Routing\Matcher\Dumper\PhpMatcherDumper',
                    'matcher_cache_class'   => 'BrodaRequestMatcher',
                    'resource_type' => 'annotation',
                ),
                $c['routing.request_context']
            );
            return $router;
        };

        $c['routing.url_generator'] = function ($c) {
            return $c['extra.router'];
        };

        $c['routing.request_matcher'] = function ($c) {
            return $c['extra.router'];
        };

    }

} 