<?php

namespace Broda\Component\Rest\Provider;

use Broda\Component\Rest\EventListener\RestResponseListener;
use Broda\Component\Rest\Loader\AnnotationClassLoader;
use Broda\Component\Rest\Loader\AnnotationDirectoryLoader;
use Broda\Component\Rest\Server\Resource;
use Broda\Component\Rest\Server\ResourceManager;
use Broda\Component\Rest\RestService;
use Broda\Component\Rest\Serializer\Construction\NaturalObjectConstructor;
use JMS\Serializer\SerializerBuilder;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Silex\Api\EventListenerProviderInterface;
use Silex\ServiceControllerResolver;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class RestServiceProvider implements ServiceProviderInterface, EventListenerProviderInterface
{

    public function register(Container $app)
    {
        if (!($app['resolver'] instanceof ServiceControllerResolver)) {
            throw new \RuntimeException('Register ServiceControllerServiceProvider first.');
        }

        foreach (array('all', 'post', 'get', 'put', 'patch', 'delete') as $method) {
            $app['rest.methods.' . $method] = $method;
        }

        $app['rest.annotation_loader'] = function () use ($app) {
            return new AnnotationDirectoryLoader(
                    new FileLocator($app['controllers_path']),
                    new AnnotationClassLoader($app['annotation.reader'], $app['rest.rm'])
            );
        };

        $app['rest'] = function() use ($app) {
            return new RestService($app['rest.serializer']);
        };

        $app['rest.rm'] = function() use ($app) {
            Resource::$defaultMethods = array(
                'all' => $app['rest.methods.all'],
                'post' => $app['rest.methods.post'],
                'get' => $app['rest.methods.get'],
                'put' => $app['rest.methods.put'],
                'patch' => $app['rest.methods.patch'],
                'delete' => $app['rest.methods.delete'],
            );

            return new ResourceManager($app['routes'], $app, $app['route_class']);
        };

        $app['rest.listener'] = function() use ($app) {
            return new RestResponseListener($app['rest'], $app['rest.annotation_loader']);
        };

        $app['rest.serializer'] = function() use ($app) {
            $builder = SerializerBuilder::create()
                            ->setObjectConstructor(NaturalObjectConstructor::create($app));

            if (isset($app['annotation.reader'])) {
                $builder->setAnnotationReader($app['annotation.reader']);
            }

            return $builder->build();
        };
    }

    public function subscribe(Container $app, EventDispatcherInterface $dispatcher)
    {
        $dispatcher->addSubscriber($app['rest.listener']);
    }

}
