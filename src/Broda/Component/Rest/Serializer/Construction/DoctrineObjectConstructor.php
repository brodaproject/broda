<?php

namespace Broda\Component\Rest\Serializer\Construction;

use Doctrine\ORM\EntityManager;
use JMS\Serializer\Construction\ObjectConstructorInterface;
use JMS\Serializer\DeserializationContext;
use JMS\Serializer\Metadata\ClassMetadata;
use JMS\Serializer\VisitorInterface;
use Pimple\Container;

/**
 * Doctrine object constructor for new (or existing) objects during deserialization.
 */
class DoctrineObjectConstructor implements ObjectConstructorInterface
{
    private $container;
    private $fallbackConstructor;

    public function __construct(Container $container, ObjectConstructorInterface $fallbackConstructor)
    {
        $this->container           = $container;
        $this->fallbackConstructor = $fallbackConstructor;
    }

    public function construct(VisitorInterface $visitor, ClassMetadata $metadata,
                              $data, array $type, DeserializationContext $context)
    {
        // Locate possible ObjectManager
        $objectManager = null;
        $ems = $this->container['orm.ems']->keys();
        foreach ($ems as $name) {
            /* @var $em EntityManager */
            $em = $this->container['orm.ems'][$name];
            if ($em->getMetadataFactory()->isTransient($metadata->name)) {
                $objectManager = $em;
                break;
            }
        }

        if (!$objectManager) {
            // No ObjectManager found, proceed with normal deserialization
            return $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);
        }

        // Managed entity, check for proxy load
        if (!is_array($data)) {
            // Single identifier, load proxy
            try {
                return $objectManager->getReference($metadata->name, $data);
            } catch (\Exception $e) {
                if ($e instanceof \Doctrine\ORM\Mapping\MappingException
                 || $e instanceof \Doctrine\Common\Persistence\Mapping\MappingException) {
                    // Metadata not exists or class not exists in namespace, proceed with normal deserialization
                    return $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);
                }
                throw $e;
            }
        }

        // Fallback to default constructor if missing identifier(s)
        try {
            $classMetadata  = $objectManager->getClassMetadata($metadata->name);
        } catch (\Exception $e) {
            if ($e instanceof \Doctrine\ORM\Mapping\MappingException
                || $e instanceof \Doctrine\Common\Persistence\Mapping\MappingException) {
                // Metadata not exists or class not exists in namespace, proceed with normal deserialization
                return $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);
            }
            throw $e;
        }
        $identifierList = array();

        foreach ($classMetadata->getIdentifierFieldNames() as $name) {
            if ( ! array_key_exists($name, $data)) {
                return $this->fallbackConstructor->construct($visitor, $metadata, $data, $type, $context);
            }

            $identifierList[$name] = $data[$name];
        }

        // Entity update, load it from database
        $object = $objectManager->find($metadata->name, $identifierList);

        $objectManager->initializeObject($object);

        return $object;
    }
}
