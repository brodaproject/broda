<?php

namespace Broda\Framework;

use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\EventListener\EsiListener;
use Symfony\Component\HttpKernel\EventListener\ExceptionListener;
use Symfony\Component\HttpKernel\EventListener\ResponseListener;
use Symfony\Component\HttpKernel\HttpCache\Esi;
use Symfony\Component\HttpKernel\HttpCache\HttpCache;
use Symfony\Component\HttpKernel\HttpCache\Store;
use Symfony\Component\HttpKernel\HttpKernel;

/**
 * Classe Configuration
 *
 * @author raphael
 */
class Configuration extends Container
{

    private $booted = false;
    private $providers = array();

    public function __construct(array $values = array())
    {
        parent::__construct();

        $sc = $this;

        $sc['logger'] = null;
        $sc['debug'] = false;
        $sc['charset'] = 'UTF-8';

        $sc['exception_controller'] = $sc->protect(function () {
            return;
        });

        $sc['dispatcher'] = function ($sc) {
            $dispatcher = new EventDispatcher();

            $dispatcher->addSubscriber(new ExceptionListener($sc['exception_controller'], $sc['logger']));
            $dispatcher->addSubscriber(new ResponseListener($sc['charset']));
            $dispatcher->addSubscriber(new \Symfony\Component\HttpKernel\EventListener\ErrorsLoggerListener('errors', $sc['logger']));

            return $dispatcher;
        };

        $sc['resolver'] = function ($sc) {
            return new ControllerResolver($sc['logger']);
        };

        $sc['service_resolver'] = function ($sc) {
            return new ServiceResolver($sc);
        };

        $sc['request_stack'] = function () {
            return new RequestStack();
        };

        $sc['kernel'] = function ($sc) {
            return new HttpKernel($sc['dispatcher'], $sc['resolver'], $sc['request_stack']);
        };

        $sc->register(new Provider\AnnotationServiceProvider(array($values['loader'])));
        $sc->register(new Provider\RoutingServiceProvider());

        foreach ($values as $key => $value) {
            $this->offsetSet($key, $value);
        }
    }

    public function enableCache($dir)
    {
        $this['cache.esi'] = function () {
            return new Esi();
        };

        $this['cache.store'] = function () use ($dir) {
            return new Store($dir);
        };

        $this->extend('dispatcher', function (EventDispatcherInterface $dispatcher, $sc) {
            $dispatcher->addSubscriber(new EsiListener($sc['cache.esi']));
            return $dispatcher;
        });
        $this->extend('kernel', function ($kernel, $sc) {
            return new HttpCache($kernel, $sc['cache.store'], $sc['cache.esi'], array(
                'debug' => $sc['debug']
            ));
        });
    }

    public function register(ServiceProviderInterface $provider, array $values = array())
    {
        if ($provider instanceof EventSubscriberProviderInterface) {
            $this->extend('dispatcher', function (EventDispatcherInterface $dispatcher, $sc) use ($provider) {
                $provider->subscribe($sc, $dispatcher);
                return $dispatcher;
            });
        }

        parent::register($provider, $values);

        return $this;
    }

}
