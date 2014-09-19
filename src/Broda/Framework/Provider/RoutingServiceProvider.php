<?php

namespace Broda\Framework\Provider;

use Broda\Framework\EventSubscriberProviderInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Sensio\Bundle\FrameworkExtraBundle\Routing\AnnotatedRouteControllerLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Router;

/**
 * Symfony Routing component Provider.
 *
 * @author raphael
 */
class RoutingServiceProvider implements ServiceProviderInterface, EventSubscriberProviderInterface
{

    public function register(Container $sc)
    {
        $sc['router.loader'] = function () use ($sc) {
            return new AnnotationDirectoryLoader(
                    new FileLocator($sc['controllers_dir']),
                    new AnnotatedRouteControllerLoader($sc['annotation.reader'])
            );
        };

        $sc['router'] = function () use ($sc) {
            $router = new Router(
                    $sc['router.loader'],
                    '',
                    array(
                        'cache_dir'             => $sc['cache_dir'],
                        'debug'                 => $sc['debug'],
                        'generator_class'       => 'Symfony\Component\Routing\Generator\UrlGenerator',
                        'generator_base_class'  => 'Symfony\Component\Routing\Generator\UrlGenerator',
                        'generator_cache_class' => 'BrodaUrlGenerator',
                        'matcher_class'         => 'Broda\Framework\Provider\Routing\RedirectableUrlMatcher',
                        'matcher_base_class'    => 'Broda\Framework\Provider\Routing\RedirectableUrlMatcher',
                        'matcher_cache_class'   => 'BrodaUrlMatcher',
                    ),
                    $sc['request_context']
            );
            return $router;
        };

        $sc['url_generator'] = function () use ($sc) {
            return $sc['router'];
        };

        $sc['url_matcher'] = function () use ($sc) {
            return $sc['router'];
        };

        $sc['request_context'] = function () use ($sc) {
            $context = new RequestContext();

            $context->setHttpPort(isset($sc['request.http_port']) ? $sc['request.http_port'] : 80);
            $context->setHttpsPort(isset($sc['request.https_port']) ? $sc['request.https_port'] : 443);

            return $context;
        };

        $sc['routing.listener'] = function () use ($sc) {
            $urlMatcher = new Routing\LazyUrlMatcher(function () use ($sc) {
                return $sc['router'];
            });

            return new RouterListener($urlMatcher, $sc['request_context'], $sc['logger'],
                    $sc['request_stack']);
        };
    }

    public function subscribe(Container $sc, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addSubscriber($sc['routing.listener']);
    }

}
