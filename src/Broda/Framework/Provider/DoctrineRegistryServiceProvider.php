<?php

namespace Broda\Framework\Provider;

use Broda\Framework\Provider\Doctrine\Registry;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class DoctrineRegistryServiceProvider implements ServiceProviderInterface
{

    public function register(Container $sc)
    {
        /**
         * Serviço que funciona como um registro global de todas
         * as conexões e objectmanagers do Doctrine.
         *
         * @var \Doctrine\Common\Persistence\ManagerRegistry
         */
        $sc['doctrine.registry'] = function () use ($sc) {
            $connections = array_map(function ($conn) {
                return 'dbs:' . $conn;
            }, $sc['dbs']->keys());

            $managers = array_map(function ($mng) {
                return 'orm.ems:' . $mng;
            }, $sc['orm.ems']->keys());

            return new Registry(
                    $sc,
                    $connections,
                    $managers,
                    $sc['dbs.default'],
                    $sc['orm.ems.default']
            );
        };

    }

}
