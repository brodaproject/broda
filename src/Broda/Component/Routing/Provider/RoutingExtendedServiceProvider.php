<?php

namespace Broda\Component\Routing\Provider;

use Broda\Component\Routing\Loader\AnnotationClassLoader;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
use Symfony\Component\Routing\Router;

/**
 * Description of RoutingExtendedServiceProvider
 *
 * @author raphael
 */
class RoutingExtendedServiceProvider implements ServiceProviderInterface
{
    public function register(Container $app)
    {

        $generator_class = get_class($app['url_generator']);
        $matcher_class = get_class($app['url_matcher']);

        $app['controllers_path'] = array();
        $app['router.options'] = array();

        $app['loader.annotation'] = function () use ($app) {
            return new AnnotationDirectoryLoader(
                    new FileLocator($app['controllers_path']),
                    new AnnotationClassLoader($app['annotation.reader'])
            );
        };

        $app['router'] = function () use ($app, $generator_class, $matcher_class) {
            $router = new Router(
                    $app['loader.annotation'],
                    '',
                    array(
                        'cache_dir' => $app['router.options']['cache_dir'],
                        'debug' => $app['debug'],
                        'generator_class' => $generator_class,
                        'generator_cache_class' => 'BrodaUrlGenerator',
                        'matcher_class' => $matcher_class,
                        'matcher_cache_class' => 'BrodaUrlMatcher',
                    ),
                    $app['request_context']
            );
            return $router;
        };

        unset($app['routes']);
        $app['routes'] = function () use ($app) {
            return $app['router']->getRouteCollection();
        };

        unset($app['url_matcher'], $app['url_generator']);
        $app['url_matcher'] = function () use ($app) {
            return $app['router'];
        };
        $app['url_generator'] = function () use ($app) {
            return $app['router'];
        };
    }
}
