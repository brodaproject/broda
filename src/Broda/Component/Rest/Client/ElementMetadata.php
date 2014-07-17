<?php

namespace Broda\Component\Rest\Client;

/**
 * Classe ElementMetadata
 *
 * @author raphael
 */
class ElementMetadata
{
    const ACCESS_DIRECT = 1;
    const ACCESS_PUBLIC_METHOD = 2;

    /**
     * Nome da classe
     *
     * @var string
     */
    public $class;

    /**
     * Nome da propriedade que é o identifier do objeto
     *
     * @var string
     */
    public $identifier;

    /**
     * Tipo de acesso à propriedade identifier do objeto
     *
     * Pode ser ElementMetadata::ACCESS_DIRECT para acesso via reflection
     * ou ElementMetadata::ACCESS_PUBLIC_METHOD para acesso pelo seu getter, se existir
     *
     * @var int
     */
    public $access = self::ACCESS_DIRECT;

    public function getIdentifier($object) {
        if (!($object instanceof $this->class)) {
            throw new \RuntimeException(sprintf('Classe %s não suportada (esperado %s)', get_class($object), $this->class));
        }
        $reflProp = new \ReflectionProperty($this->class, $this->identifier);
        $reflProp->setAccessible(true);

        return $reflProp->getValue($object);
    }
}
