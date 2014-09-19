<?php

namespace Broda\Framework\Controller\Provider;

use Broda\Framework\Controller\ServiceControllerResolver;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Possibilita usar serviços do container como controllers.
 *
 * O formato do controller deve ser: 'nome_do_servico:metodo'
 *
 * @author raphael hardt
 */
class ServiceControllerServiceProvider implements ServiceProviderInterface
{
    public function register(Container $sc)
    {
        $sc->extend('resolver', function ($resolver, $sc) {
            return new ServiceControllerResolver($resolver, $sc['service_resolver']);
        });
    }

} 