<?php

namespace Broda\Core\Provider\DoctrineExtensions;

use Broda\Core\Container as BrodaContainer;
use Broda\Core\Provider\Doctrine\Container\DoctrineSubscriberProviderInterface;
use Doctrine\Common\EventManager;
use Gedmo\Translatable\TranslatableListener;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class TranslatableProvider implements ServiceProviderInterface, DoctrineSubscriberProviderInterface
{

    public function register(Container $c)
    {
        $c['doctrine_extensions.translatable.listener'] = function ($c) {
            $listener = new TranslatableListener();
            $listener->setTranslatableLocale($c['locale']);
            $listener->setDefaultLocale($c['locale']);
            $listener->setAnnotationReader($c['annotation.reader']);
            return $listener;
        };
    }

    public function subscribeDoctrine(BrodaContainer $c, $connectionName, $isDefault, EventManager $evm)
    {
        $evm->addEventSubscriber($c['doctrine_extensions.translatable.listener']);
    }

}