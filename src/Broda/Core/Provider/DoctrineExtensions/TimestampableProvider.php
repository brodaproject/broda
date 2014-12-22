<?php

namespace Broda\Core\Provider\DoctrineExtensions;


use Broda\Core\Container as BrodaContainer;
use Broda\Core\Provider\Doctrine\Container\DoctrineSubscriberProviderInterface;
use Doctrine\Common\EventManager;
use Gedmo\Timestampable\TimestampableListener;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class TimestampableProvider implements ServiceProviderInterface, DoctrineSubscriberProviderInterface
{

    public function register(Container $c)
    {
        $c['doctrine_extensions.timestampable.listener'] = function ($c) {
            $listener = new TimestampableListener();
            $listener->setAnnotationReader($c['annotation.reader']);
            return $listener;
        };
    }

    public function subscribeDoctrine(BrodaContainer $c, $connectionName, $isDefault, EventManager $evm)
    {
        $evm->addEventSubscriber($c['doctrine_extensions.timestampable.listener']);
    }

} 