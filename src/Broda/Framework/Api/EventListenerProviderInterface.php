<?php

namespace Broda\Framework\Api;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Pimple\Container;

/**
 * Interface for event listener providers.
 *
 * Based on Silex's Api by Fabien Potencier <fabien@symfony.com>
 *
 * @author raphael
 */
interface EventListenerProviderInterface
{
    public function subscribe(Container $app, EventDispatcherInterface $dispatcher);
}
