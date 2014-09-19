<?php

namespace Broda\Framework\Provider;

use Broda\Framework\EventSubscriberProviderInterface;
use Doctrine\Common\Annotations\Reader;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\HttpCacheListener;
use Sensio\Bundle\FrameworkExtraBundle\EventListener\ParamConverterListener;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DateTimeParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\DoctrineParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Classe FrameworkExtraProvider
 *
 * @author raphael
 */
class FrameworkExtraProvider implements ServiceProviderInterface, EventSubscriberProviderInterface
{

    public function register(Container $sc)
    {

        if (!isset($sc['annotation.reader']) || !$sc['annotation.reader'] instanceof Reader) {
            throw new \LogicException('The service \'annotation.reader\' must be an instance of Doctrine\Common\Annotations\Reader');
        }

        $sc['extra.paramconverter.auto_convert'] = true;
        $sc['extra.paramconverter.manager'] = function ($sc) {
            $converterManager = new ParamConverterManager();
            if (isset($sc['doctrine.registry'])) {
                $converterManager->add(new DoctrineParamConverter($sc['doctrine.registry']), 10, 'doctrine.orm');
            }
            $converterManager->add(new DateTimeParamConverter(), -5, 'datetime');
            return $converterManager;
        };

        $sc['extra.controller_listener'] = function ($sc) {
            return new ControllerListener($sc['annotation.reader']);
        };

        $sc['extra.paramconverter_listener'] = function ($sc) {
            return new ParamConverterListener($sc['extra.paramconverter.manager'], $sc['extra.paramconverter.auto_convert']);
        };

        $sc['extra.httpcache_listener'] = function () {
            return new HttpCacheListener();
        };

        /*$sc['extra.security_listener'] = function ($sc) {
            return new SecurityListener();
        };*/
    }

    public function subscribe(Container $sc, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addSubscriber($sc['extra.controller_listener']);
        $dispatcher->addSubscriber($sc['extra.paramconverter_listener']);
        $dispatcher->addSubscriber($sc['extra.httpcache_listener']);
        //$dispatcher->addSubscriber($sc['extra.security_listener']);
    }

}
