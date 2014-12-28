<?php

namespace Broda\Core\Provider\Doctrine\Container;


use Pimple\Container;
use Doctrine\Common\EventManager;

interface DoctrineSubscriberProviderInterface
{
    /**
     * Registra listeners nas conexões do doctrine, passando o container de serviços
     * e o nome da conexão.
     *
     * Para registrar os listeners em apenas uma conexão, verifique com switch ou if
     * pelo nome da conexão em $connectionName.
     *
     * @param Container $container
     * @param string $connectionName
     * @param boolean $isDefault
     * @param EventManager $eventManager
     * @return mixed
     */
    public function subscribeDoctrine(Container $container, $connectionName, $isDefault, EventManager $eventManager);

} 