<?php

namespace Broda\Core\Provider\Doctrine;

use Broda\Core\Provider\Doctrine\Registry\OrmRegistry;
use Doctrine\Common\Persistence\Mapping\Driver\MappingDriverChain;
use Doctrine\Common\Persistence\Mapping\Driver\StaticPHPDriver;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\DefaultEntityListenerResolver;
use Doctrine\ORM\Mapping\DefaultNamingStrategy;
use Doctrine\ORM\Mapping\DefaultQuoteStrategy;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\Driver\SimplifiedXmlDriver;
use Doctrine\ORM\Mapping\Driver\SimplifiedYamlDriver;
use Doctrine\ORM\Mapping\Driver\XmlDriver;
use Doctrine\ORM\Mapping\Driver\YamlDriver;
use Doctrine\ORM\Repository\DefaultRepositoryFactory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntityValidator;
use Symfony\Bridge\Doctrine\Validator\DoctrineInitializer;

class OrmProvider extends DbalProvider implements ServiceProviderInterface
{

    public function register(Container $c)
    {
        parent::register($c);

        $c['orm.options'] = array();

        $c['orm.types'] = array();
        $c['orm.proxies_dir'] = sys_get_temp_dir();
        $c['orm.proxies_namespace'] = 'DoctrineProxy';
        $c['orm.auto_generate_proxies'] = $c->factory(function ($c) {
            return $c['debug'];
        });

        $c['orm.custom.functions.string'] = array();
        $c['orm.custom.functions.numeric'] = array();
        $c['orm.custom.functions.datetime'] = array();
        $c['orm.custom.hydration_modes'] = array();
        $c['orm.class_metadata_factory_name'] = 'Doctrine\ORM\Mapping\ClassMetadataFactory';
        $c['orm.default_repository_class'] = 'Doctrine\ORM\EntityRepository';

        $c['orm.initialize'] = $c->protect(function () use ($c) {
            static $initialized = false;

            if ($initialized) {
                return;
            }

            $initialized = true;

            if (!isset($c['orm.options'])) {
                $c['orm.options'] = array('default' => array());
            } elseif (!is_array(reset($c['orm.options']))) {
                // Se o primeiro elemento do array de opções não for um array,
                // muito provavelmente o usuário está colocando as options de
                // uma única conexão, então usar como default
                $c['orm.options'] = array('default' => $c['orm.options']);
            }

            $tmp = $c['orm.options'];
            foreach ($tmp as $name => &$options) {
                if (!isset($options['connection'])) {
                    throw new \LogicException("Missing 'connection' param in Doctrine ORM in $name.");
                }

                if (is_string($options['connection'])) {
                    $options['connection'] = $c['dbal.options'][$options['connection']];
                } elseif (!is_array($options['connection']) && !$options['connection'] instanceof Connection) {
                    throw new \LogicException("Param 'connection' in $name must be a string, array or a Connection instance.");
                }
            }
            $c['orm.options'] = $tmp;

            $c['orm.defaultName'] = key($c['orm.options']);
        });

        $c['orm.ems'] = function ($c) {
            $c['orm.initialize']();

            $ems = new Container();
            foreach ($c['orm.options'] as $name => $options) {
                $config     = $c['orm.configs'][$name];
                $connection = $options['connection'];

                $ems[$name] = function ($ems) use ($connection, $config) {
                    return EntityManager::create($connection, $config);
                };
            }

            return $ems;
        };

        $c['orm.configs'] = function ($c) {
            $c['orm.initialize']();

            $configs = new Container();
            foreach ($c['orm.options'] as $name => $options) {
                $configs[$name] = $config = new Configuration();

                $config->setProxyDir($c['orm.proxies_dir']);
                $config->setProxyNamespace($c['orm.proxies_namespace']);
                $config->setAutoGenerateProxyClasses($c['orm.auto_generate_proxies']);

                $config->setCustomStringFunctions($c['orm.custom.functions.string']);
                $config->setCustomNumericFunctions($c['orm.custom.functions.numeric']);
                $config->setCustomDatetimeFunctions($c['orm.custom.functions.datetime']);
                $config->setCustomHydrationModes($c['orm.custom.hydration_modes']);

                $config->setClassMetadataFactoryName($c['orm.class_metadata_factory_name']);
                $config->setDefaultRepositoryClassName($c['orm.default_repository_class']);

                $config->setEntityListenerResolver($c['orm.entity_listener_resolver']);
                $config->setRepositoryFactory($c['orm.repository_factory']);

                $config->setNamingStrategy($c['orm.strategy.naming']);
                $config->setQuoteStrategy($c['orm.strategy.quote']);

                $chain = new MappingDriverChain();
                $config->setMetadataDriverImpl($chain);

                foreach ((array) $options['mappings'] as $entity) {
                    if (!is_array($entity)) {
                        throw new \InvalidArgumentException(
                            "The 'orm.options' option 'mappings' should be an array of arrays."
                        );
                    }

                    if (isset($entity['alias'])) {
                        $config->addEntityNamespace($entity['alias'], $entity['namespace']);
                    }

                    switch ($entity['type']) {
                        case 'annotation':
                            if (isset($c['annotation.reader'])) {
                                $driver = new AnnotationDriver($c['annotation.reader'], (array)$entity['path']);
                            } else {
                                $simple =
                                    isset($entity['use_simple_annotation_reader'])
                                        ? $entity['use_simple_annotation_reader']
                                        : true;
                                $driver = $config->newDefaultAnnotationDriver((array)$entity['path'], $simple);
                            }
                            break;
                        case 'yml':
                            $driver = new YamlDriver($entity['path']);
                            break;
                        case 'simple_yml':
                            $driver = new SimplifiedYamlDriver(array($entity['path'] => $entity['namespace']));
                            break;
                        case 'xml':
                            $driver = new XmlDriver($entity['path']);
                            break;
                        case 'simple_xml':
                            $driver = new SimplifiedXmlDriver(array($entity['path'] => $entity['namespace']));
                            break;
                        case 'php':
                            $driver = new StaticPHPDriver($entity['path']);
                            break;
                        default:
                            throw new \InvalidArgumentException(sprintf('"%s" is not a recognized driver', $entity['type']));
                    }
                    $chain->addDriver($driver, $entity['namespace']);

                }

            }

            foreach ((array) $c['orm.types'] as $typeName => $typeClass) {
                if (Type::hasType($typeName)) {
                    Type::overrideType($typeName, $typeClass);
                } else {
                    Type::addType($typeName, $typeClass);
                }
            }

            return $configs;
        };

        $c['orm.strategy.naming'] = function($c) {
            return new DefaultNamingStrategy();
        };
        $c['orm.strategy.quote'] = function($c) {
            return new DefaultQuoteStrategy();
        };
        $c['orm.entity_listener_resolver'] = function($c) {
            return new DefaultEntityListenerResolver();
        };
        $c['orm.repository_factory'] = function($c) {
            return new DefaultRepositoryFactory();
        };

        $c->extend('doctrine.registry', function ($registry, $c) {
            $c['orm.initialize']();

            return new OrmRegistry(
                $c['dbal.conns'],
                $c['orm.ems'],
                'Doctrine\Common\Persistence\Proxy',
                array_keys($c['dbal.options']),
                array_keys($c['orm.options']),
                $c['dbal.defaultName'],
                $c['orm.defaultName']
            );
        });

        // TODO passar pro Container\ValidatorExtensionableProviderInterface
        if (isset($c['validator.object_initializers'])) {
            $c->extend('validator.object_initializers', function ($initializers, $c) {
                $initializers[] = new DoctrineInitializer($c['doctrine.registry']);

                $c['doctrine.orm.validator.unique'] = new UniqueEntityValidator($c['doctrine.registry']);

                return $initializers;
            });
        }
    }

} 