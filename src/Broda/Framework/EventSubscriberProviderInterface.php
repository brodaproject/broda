<?php

namespace Broda\Framework;


use Pimple\Container;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface EventSubscriberProviderInterface
{

    public function subscribe(Container $sc, EventDispatcherInterface $dispatcher);

} 