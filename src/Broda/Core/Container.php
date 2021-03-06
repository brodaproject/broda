<?php

namespace Broda\Core;

use Broda\Core\Container\BootableProviderInterface;
use Broda\Core\Container\FormExtensionableProviderInterface;
use Broda\Core\Container\RouteProviderInterface;
use Broda\Core\Container\SubscriberProviderInterface;
use Broda\Core\Provider\Doctrine\Container\DoctrineSubscriberProviderInterface;
use Broda\Core\Provider\Twig\Container\TwigExtensionableProviderInterface;
use Doctrine\Common\Persistence\ConnectionRegistry;
use Doctrine\DBAL\Connection;
use Pimple\Container as BaseContainer;
use Pimple\ServiceProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormFactoryBuilderInterface;
use Symfony\Component\Routing\RouteCollection;

class Container extends BaseContainer
{
    /**
     * @var BootableProviderInterface[]
     */
    private $bootableProviders = array();

    /**
     * @var RouteProviderInterface[]
     */
    private $routeProviders = array();

    /**
     * @var SubscriberProviderInterface[]
     */
    private $subscribeProviders = array();

    /**
     * @var DoctrineSubscriberProviderInterface[]
     */
    private $doctrineSubscribeProviders = array();

    /**
     * @var TwigExtensionableProviderInterface[]
     */
    private $twigExtensionsProviders = array();

    /**
     * @var FormExtensionableProviderInterface[]
     */
    private $formExtensionsProviders = array();

    private $booted = false;

    /**
     * {@inheritdoc}
     */
    public function register(ServiceProviderInterface $provider, array $values = array())
    {
        // Não pode ser "elseif" pois um provider pode ser ao mesmo
        // tempo todos os tipos de provider
        if ($provider instanceof BootableProviderInterface) {
            if (!isset($this->bootableProviders[$provider->getPriority()])) {
                $this->bootableProviders[$provider->getPriority()] = array();
            }
            $this->bootableProviders[$provider->getPriority()][]  = $provider;
        }

        if ($provider instanceof RouteProviderInterface) {
            $this->routeProviders[] = $provider;
        }

        if ($provider instanceof SubscriberProviderInterface) {
            $this->subscribeProviders[] = $provider;
        }

        if ($provider instanceof DoctrineSubscriberProviderInterface) {
            $this->doctrineSubscribeProviders[] = $provider;
        }

        if ($provider instanceof TwigExtensionableProviderInterface) {
            $this->twigExtensionsProviders[] = $provider;
        }

        if ($provider instanceof FormExtensionableProviderInterface) {
            $this->formExtensionsProviders[] = $provider;
        }

        parent::register($provider, $values);

        return $this;
    }

    /**
     * Inicializa o container.
     *
     * Deve ser chamado no front controller, antes de usar os serviços.
     */
    public function boot()
    {
        if ($this->booted) {
            return;
        }

        $this->booted = true;

        $routeProviders = $this->routeProviders;
        $subscribeProviders = $this->subscribeProviders;
        $docSubscribeProviders = $this->doctrineSubscribeProviders;
        $twigProviders = $this->twigExtensionsProviders;
        $formProviders = $this->formExtensionsProviders;

        // Primeiro adiciona rotas e eventos dos providers
        if (isset($this['routes'])) {
            $this->extend('routes', function (RouteCollection $routes, $c) use (&$routeProviders) {
                foreach ($routeProviders as $provider) {
                    /* @var $provider RouteProviderInterface */
                    $provider->route($c, $routes);
                }
                return $routes;
            });
        }

        if (isset($this['dispatcher'])) {
            $this->extend('dispatcher', function (EventDispatcherInterface $dispatcher, $c) use (&$subscribeProviders) {
                foreach ($subscribeProviders as $provider) {
                    /* @var $provider SubscriberProviderInterface */
                    $provider->subscribe($c, $dispatcher);
                }
                return $dispatcher;
            });
        }

        if (isset($this['doctrine.registry'])) {
            $this->extend('doctrine.registry', function (ConnectionRegistry $registry, $c) use (&$docSubscribeProviders) {
                foreach ($registry->getConnections() as $name => $connection) {
                    /* @var $connection Connection */
                    foreach ($docSubscribeProviders as $provider) {
                        /* @var $provider DoctrineSubscriberProviderInterface */
                        $provider->subscribeDoctrine(
                            $c,
                            $name,
                            $name === $registry->getDefaultConnectionName(),
                            $connection->getEventManager()
                        );
                    }
                }
                return $registry;
            });
        }

        if (isset($this['twig'])) {
            $this->extend('twig', function (\Twig_Environment $twig, $c) use (&$twigProviders) {
                foreach ($twigProviders as $provider) {
                    /* @var $provider TwigExtensionableProviderInterface */
                    $provider->twigExtensions($c, $twig);
                }
                return $twig;
            });
        }

        if (isset($this['form.factory_builder'])) {
            $this->extend('form.factory_builder', function (FormFactoryBuilderInterface $builder, $c) use (&$formProviders) {
                foreach ($formProviders as $provider) {
                    /* @var $provider FormExtensionableProviderInterface */
                    $provider->formExtensions($c, $builder);
                }
                return $builder;
            });
        }

        // Depois boota todos os providers
        krsort($this->bootableProviders); // Ordena os providers por prioridade
        foreach ($this->bootableProviders as $priority => $providers) {
            foreach ($providers as $provider) {
                if ($provider instanceof BootableProviderInterface) {
                    $provider->boot($this);
                }
            }
        }
    }
} 