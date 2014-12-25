<?php

namespace Broda\Core\Provider\Symfony;

use Broda\Core\Container\BootableProviderInterface;
use Broda\Core\Container\SubscriberProviderInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Debug\Debug;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\EventListener\DebugHandlersListener;


class DebugProvider implements ServiceProviderInterface, BootableProviderInterface, SubscriberProviderInterface
{
    private $enabled = true;

    function __construct($enabled = true)
    {
        $this->enabled = $enabled;
    }

    public function register(Container $c)
    {
        if (!isset($c['debug'])) {
            $c['debug'] = $this->enabled;
        }

        $c['debug.error_level'] = E_ALL & E_STRICT;
        $c['debug.display_errors'] = true;

        // Mantenha null para que o Symfony auto-detecte o handler correto
        // (no caso do symfony, é o do HttpKernel::terminateWithException)
        $c['debug.exception_handler'] = null;

        $c['debug.handlers_listener'] = function ($c) {
            return new DebugHandlersListener(
                $c['debug.exception_handler'],
                $c['logger'],
                $c['debug.error_level'],
                $c['debug.error_level'],
                true
            );
        };
    }

    public function subscribe(Container $c, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addSubscriber($c['debug.handlers_listener']);
    }

    public function boot(Container $c)
    {
        // Confio no parametro 'debug' pois ele pode ter mudado durante
        // a fase de configuração por algum provider ou pelo proprio user
        if ($c['debug']) {
            Debug::enable($c['debug.error_level'], $c['debug.display_errors']);
        }
    }

    public function getPriority()
    {
        return 1024;
    }

} 