<?php

namespace Broda\Core\Provider\DoctrineExtensions;


use Broda\Core\Container as BrodaContainer;
use Broda\Core\Provider\Doctrine\Container\DoctrineSubscriberProviderInterface;
use Doctrine\Common\EventManager;
use Gedmo\Sortable\SortableListener;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class SortableProvider implements ServiceProviderInterface, DoctrineSubscriberProviderInterface
{

    public function register(Container $c)
    {
        $c['doctrine_extensions.sortable.listener'] = function ($c) {
            $listener = new SortableListener();
            $listener->setAnnotationReader($c['annotation.reader']);
            return $listener;
        };
    }

    public function subscribeDoctrine(BrodaContainer $c, $connectionName, $isDefault, EventManager $evm)
    {
        $evm->addEventSubscriber($c['doctrine_extensions.sortable.listener']);
    }

} 