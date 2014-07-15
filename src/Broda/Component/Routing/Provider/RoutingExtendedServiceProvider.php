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

        if (!isset($app['request_context'])) {
            throw new \LogicException('Register RoutingServiceProvider first');
        }

        $app['controllers_path'] = array();
        $app['router.options'] = array();

        $app['router.loader'] = function () use ($app) {
            return new AnnotationDirectoryLoader(
                    new FileLocator($app['controllers_path']),
                    new AnnotationClassLoader($app['annotation.reader'])
            );
        };

        $app['router'] = function () use ($app) {
            $router = new Router(
                    $app['router.loader'],
                    '',
                    array(
                        'cache_dir' => $app['router.options']['cache_dir'],
                        'debug' => $app['debug'],
                        'generator_class' => 'Symfony\Component\Routing\Generator\UrlGenerator',
                        'generator_cache_class' => 'BrodaUrlGenerator',
                        'matcher_class' => 'Silex\Provider\Routing\RedirectableUrlMatcher',
                        'matcher_cache_class' => 'BrodaUrlMatcher',
                    ),
                    $app['request_context']
            );
            return $router;
        };

        $app->extend('routes', function ($oldRoutes) use ($app) {
            $routes = $app['router']->getRouteCollection();
            $routes->addCollection($oldRoutes); // aggregates old-school defined routes
            return $routes;
        });

        $app->extend('url_matcher', function () use ($app) {
            return $app['router'];
        });
        $app->extend('url_generator', function () use ($app) {
            return $app['router'];
        });
    }
}
