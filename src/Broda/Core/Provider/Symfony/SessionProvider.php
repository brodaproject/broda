<?php

namespace Broda\Core\Provider\Symfony;

use Broda\Core\Container\SubscriberProviderInterface;
use Broda\Core\Provider\Symfony\Session\SessionListener;
use Broda\Core\Provider\Symfony\Session\TestSessionListener;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Symfony HttpFoundation component Provider for sessions.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class SessionProvider implements ServiceProviderInterface, SubscriberProviderInterface
{
    public function register(Container $c)
    {
        $c['session.test'] = false;

        $c['session'] = function ($c) {
            if (!isset($c['session.storage'])) {
                if ($c['session.test']) {
                    $c['session.storage'] = $c['session.storage.test'];
                } else {
                    $c['session.storage'] = $c['session.storage.native'];
                }
            }

            return new Session($c['session.storage']);
        };

        $c['session.storage.handler'] = function ($c) {
            return new NativeFileSessionHandler($c['session.storage.save_path']);
        };

        $c['session.storage.native'] = function ($c) {
            return new NativeSessionStorage(
                $c['session.storage.options'],
                $c['session.storage.handler']
            );
        };

        $c['session.storage.test'] = function () {
            return new MockFileSessionStorage();
        };

        $c['session.listener'] = function ($c) {
            return new SessionListener($c);
        };

        $c['session.listener.test'] = function ($c) {
            return new TestSessionListener($c);
        };

        $c['session.storage.options'] = array();
        $c['session.default_locale'] = 'en';
        $c['session.storage.save_path'] = null;
    }

    public function subscribe(Container $c, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addSubscriber($c['session.listener']);

        if ($c['session.test']) {
            $dispatcher->addSubscriber($c['session.listener.test']);
        }
    }
}