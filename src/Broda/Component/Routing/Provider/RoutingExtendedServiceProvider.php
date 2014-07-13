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
class RoutingExtendedServiceProvider extends ServiceProviderInterface
{
    public function register(Container $app)
    {
        $app['controllers_path'] = array();

        $app['loader.annotation'] = function () use ($app) {
            return new AnnotationDirectoryLoader(
                    new FileLocator($app['controllers_path']),
                    new AnnotationClassLoader($app['annotation.reader'])
            );
        };

        $app['router'] = function () use ($app) {
            $router = new Router(
                    $app['loader.annotation'],
                    '*.php',
                    array(
                        'cache_dir' => $app['router.options']['cache_dir'],
                        'debug' => $app['debug'],
                        'generator_class' => get_class($app['url_generator']),
                        'generator_cache_class' => 'BrodaUrlGenerator',
                        'matcher_class' => get_class($app['url_matcher']),
                        'matcher_cache_class' => 'BrodaUrlMatcher',
                    ),
                    $app['request_context']
            );
            return $router;
        };

        $app->extend('url_matcher', function ($matcher) use ($app) {
            return $app['router'];
        });
        $app->extend('url_generator', function ($generator) use ($app) {
            return $app['router'];
        });
    }
}
