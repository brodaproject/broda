<?php

namespace Broda\Framework\Provider;

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
class FrameworkExtraProvider implements ServiceProviderInterface
{

    public function register(Container $sc)
    {

        if (!isset($sc['annotation.reader']) || !$sc['annotation.reader'] instanceof Reader) {
            throw new \LogicException('The service \'annotation.reader\' must be an instance of Doctrine\Common\Annotations\Reader');
        }

        $provider = $this;
        $sc->extend('dispatcher',
                function ($dispatcher) use ($sc, $provider) {
            $provider->subscribe($sc, $dispatcher);
            return $dispatcher;
        });

        $sc['converter.auto_convert'] = true;
        $sc['converter_manager'] = function ($sc) {
            $converterManager = new ParamConverterManager();
            if (isset($sc['doctrine.registry'])) {
                $converterManager->add(new DoctrineParamConverter($sc['doctrine.registry']));
            }
            $converterManager->add(new DateTimeParamConverter());
        };
    }

    public function subscribe(Container $sc, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addSubscriber(new ControllerListener($sc['annotation.reader']));
        $dispatcher->addSubscriber(new ParamConverterListener($sc['converter_manager'], $sc['converter.auto_convert']));
        $dispatcher->addSubscriber(new HttpCacheListener());
        //$dispatcher->addSubscriber(new SecurityListener());
    }

}
