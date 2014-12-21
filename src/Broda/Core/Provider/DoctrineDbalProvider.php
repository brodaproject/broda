<?php

namespace Broda\Core\Provider;

use Broda\Core\Provider\Doctrine\DbalRegistry;
use Doctrine\Common\EventManager;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Bridge\Doctrine\Logger\DbalLogger;

class DoctrineDbalProvider implements ServiceProviderInterface
{

    public function register(Container $c)
    {
        $c['dbal.options'] = array();

        $c['dbal.initialize'] = $c->protect(function () use ($c) {
            static $initialized = false;

            if ($initialized) {
                return;
            }

            $initialized = true;

            if (!isset($c['dbal.options'])) {
                $c['dbal.options'] = array('default' => array());
            } elseif (!is_array(reset($c['dbal.options']))) {
                // Se o primeiro elemento do array de opções não for um array,
                // muito provavelmente o usuário está colocando as options de
                // uma única conexão, então usar como default
                $c['dbal.options'] = array('default' => $c['dbal.options']);
            }

            $c['dbal.defaultName'] = key($c['dbal.options']);
        });

        $c['dbal.conns'] = function ($c) {
            $c['dbal.initialize']();

            $dbs = new Container();
            foreach ($c['dbal.options'] as $name => $options) {
                $config = $c['dbal.configs'][$name];
                $evm    = $c['dbal.evms'][$name];

                $dbs[$name] = function ($dbs) use ($options, $config, $evm) {
                    return DriverManager::getConnection($options, $config, $evm);
                };
            }

            return $dbs;
        };

        $c['dbal.configs'] = function ($c) {
            $c['dbal.initialize']();

            $configs = new Container();
            foreach ($c['dbal.options'] as $name => $options) {
                $configs[$name] = new Configuration();

                if (isset($c['logger']) && class_exists('Symfony\Bridge\Doctrine\Logger\DbalLogger')) {
                    $configs[$name]->setSQLLogger(new DbalLogger($c['logger'], isset($c['stopwatch']) ? $c['stopwatch'] : null));
                }
            }

            return $configs;
        };

        $c['dbal.evms'] = function ($c) {
            $c['dbal.initialize']();

            $emvs = new Container();
            foreach ($c['dbal.options'] as $name => $options) {
                $emvs[$name] = new EventManager();
            }

            return $emvs;
        };

        $c['doctrine.registry'] = function ($c) {
            $c['dbal.initialize']();

            return new DbalRegistry(
                $c, array_keys($c['dbal.options']), $c['dbal.defaultName']
            );
        };
    }

} 