<?php

namespace Broda\Core\Provider\Symfony;

use Broda\Core\Container\SubscriberProviderInterface;
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
    private $c;

    public function register(Container $c)
    {
        $this->c = $c;

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

        $c['session.storage.options'] = array();
        $c['session.default_locale'] = 'en';
        $c['session.storage.save_path'] = null;
    }

    public function onEarlyKernelRequest(GetResponseEvent $event)
    {
        $event->getRequest()->setSession($this->c['session']);
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        // bootstrap the session
        if (!isset($this->c['session'])) {
            return;
        }

        /* @var $session Session */
        $session = $this->c['session'];
        $cookies = $event->getRequest()->cookies;

        if ($cookies->has($session->getName())) {
            $session->setId($cookies->get($session->getName()));
        } else {
            $session->migrate(false);
        }
    }

    public function onKernelResponse(FilterResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $session = $event->getRequest()->getSession();
        if ($session && $session->isStarted()) {
            $session->save();

            $params = session_get_cookie_params();

            $event->getResponse()->headers->setCookie(new Cookie($session->getName(), $session->getId(), 0 === $params['lifetime'] ? 0 : time() + $params['lifetime'], $params['path'], $params['domain'], $params['secure'], $params['httponly']));
        }
    }

    public function subscribe(Container $c, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addListener(KernelEvents::REQUEST, array($this, 'onEarlyKernelRequest'), 128);

        if ($c['session.test']) {
            $dispatcher->addListener(KernelEvents::REQUEST, array($this, 'onKernelRequest'), 192);
            $dispatcher->addListener(KernelEvents::RESPONSE, array($this, 'onKernelResponse'), -128);
        }
    }
}