<?php

namespace Broda\Core\Provider\Sensio\FrameworkExtra;

use Broda\Core\Container\RouteProviderInterface;
use Broda\Core\Container\SubscriberProviderInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\HttpCacheListener;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\SecurityListener;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DateTimeParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager;
use Sensio\Bundle\FrameworkExtraBundle\Routing\AnnotatedRouteControllerLoader;
use Sensio\Bundle\FrameworkExtraBundle\Security\ExpressionLanguage;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\Loader\AnnotationDirectoryLoader;
use Symfony\Component\Routing\RouteCollection;

/**
 * Classe FrameworkExtraProvider
 *
 * @author raphael
 */
class FrameworkExtraProvider implements ServiceProviderInterface, SubscriberProviderInterface, RouteProviderInterface
{

    public function register(Container $c)
    {

        $c['extra.controllers_dir'] = sys_get_temp_dir();

        $c['extra.route_loader'] = function ($c) {
            return new AnnotationDirectoryLoader(
                new FileLocator($c['extra.controllers_dir']),
                new AnnotatedRouteControllerLoader($c['annotation.reader'])
            );
        };

        $c['extra.paramconverter.auto_convert'] = true;
        $c['extra.paramconverter.manager'] = function ($c) {
            $converterManager = new ParamConverterManager();
            if (isset($c['doctrine.registry'])) {
                $converterManager->add(new DoctrineParamConverter($c['doctrine.registry']), 10, 'doctrine.orm');
            }
            $converterManager->add(new DateTimeParamConverter(), -5, 'datetime');
            return $converterManager;
        };

        $c['extra.controller_listener'] = function ($c) {
            return new ControllerListener($c['annotation.reader']);
        };

        $c['extra.paramconverter_listener'] = function ($c) {
            return new ParamConverterListener($c['extra.paramconverter.manager'], $c['extra.paramconverter.auto_convert']);
        };

        $c['extra.httpcache_listener'] = function () {
            return new HttpCacheListener();
        };

        $c['extra.security.expression_language'] = function () {
            return new ExpressionLanguage();
        };

        $c['extra.security_listener'] = function ($c) {
            return new SecurityListener(
                isset($c['security.context']) ? $c['security.context'] : null,
                $c['extra.security.expression_language'],
                isset($c['security.authentication.trust_resolver'])
                    ? $c['security.authentication.trust_resolver']
                    : null,
                isset($c['security.role_hierarchy']) ? $c['security.role_hierarchy'] : null
            );
        };
    }

    public function subscribe(Container $c, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addSubscriber($c['extra.controller_listener']);
        $dispatcher->addSubscriber($c['extra.paramconverter_listener']);
        $dispatcher->addSubscriber($c['extra.httpcache_listener']);
        $dispatcher->addSubscriber($c['extra.security_listener']);
    }

    public function route(Container $c, RouteCollection $routes)
    {
        $routes->addCollection($c['extra.route_loader']->load('', 'annotation'));
    }


}
