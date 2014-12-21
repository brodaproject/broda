<?php

namespace Broda\Core\Container;


use Pimple\Container;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Interface para todos os ServiceProvider que precisam
 * cadastrar eventos no EventDispacther do kernel.
 *
 * Para entender o uso, ver documentação do Silex na versão 2.0,
 * aqui: https://github.com/silexphp/Silex/tree/master/doc
 */
interface SubscriberProviderInterface
{
    public function subscribe(Container $c, EventDispatcherInterface $dispatcher);
} 