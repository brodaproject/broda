<?php

namespace Broda\Component\Rest\Client;

/**
 * Classe ElementMetadataFactory
 *
 * @author raphael
 */
class ElementMetadataFactory
{
    private $driver;

    public function __construct($driver = null)
    {
        $this->driver = $driver;
    }

    public function getMetadata($className)
    {
        $reflClass = new \ReflectionClass($className);
        $reader = new \Doctrine\Common\Annotations\AnnotationReader();
        $metadata = new ElementMetadata();
        $metadata->class = $className;

        foreach ($reflClass->getProperties() as $reflProp) {
            $annot = $reader->getPropertyAnnotation($reflProp, 'Broda\Component\Rest\Annotation\Client\Identifier');

            if (null !== $annot) {
                $metadata->identifier = $reflProp->getName();
                break; // o resto dos campos são ignorados
            }
        }

        if (!$metadata->identifier) {
            if (!$reflClass->hasProperty('id')) {
                throw new \RuntimeException(sprintf('A classe %s precisa de um identifier '
                        . '(ou "id" ou uma propriedade com a anotação @Identifier)', $className));
            }

            $metadata->identifier = 'id';
        }

        return $metadata;
    }
}
