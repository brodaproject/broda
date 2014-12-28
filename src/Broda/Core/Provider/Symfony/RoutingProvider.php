<?php

namespace Broda\Core\Provider\Symfony;

use Broda\Core\Container\SubscriberProviderInterface;
use Broda\Core\Provider\Symfony\Routing\RedirectableUrlMatcher;
use Broda\Core\Provider\Twig\Container\TwigExtensionableProviderInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Bridge\Twig\Extension\RoutingExtension;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

class RoutingProvider implements ServiceProviderInterface, SubscriberProviderInterface, TwigExtensionableProviderInterface
{
    public function register(Container $c)
    {
        $c['routes'] = function () {
            return new RouteCollection();
        };

        $c['routing.url_generator'] = function ($c) {
            return new UrlGenerator($c['routes'], $c['routing.request_context']);
        };

        $c['routing.request_matcher'] = function ($c) {
            return new RedirectableUrlMatcher($c['routes'], $c['routing.request_context']);
        };

        $c['routing.request_context'] = function ($c) {
            $context = new RequestContext();

            $context->setHttpPort(isset($c['request.http_port']) ? $c['request.http_port'] : 80);
            $context->setHttpsPort(isset($c['request.https_port']) ? $c['request.https_port'] : 443);

            return $context;
        };

        $c['routing.listener'] = function ($c) {
            $urlMatcher = new Routing\LazyRequestMatcher(function () use ($c) {
                return $c['routing.request_matcher'];
            });

            return new RouterListener($urlMatcher, $c['routing.request_context'], $c['logger'],
                $c['request_stack']);
        };
    }

    public function subscribe(Container $c, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addSubscriber($c['routing.listener']);
    }

    public function twigExtensions(Container $c, \Twig_Environment $twig)
    {
        if (class_exists('Symfony\Bridge\Twig\Extension\RoutingExtension')) {
            $twig->addExtension(new RoutingExtension($c['routing.url_generator']));
        }
    }


} 