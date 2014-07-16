<?php

namespace Broda\Component\Rest\Client;

/**
 * Classe AbstractElement
 *
 * @author raphael
 */
abstract class AbstractElement implements ElementInterface
{

    /**
     *
     * @var ResourceInterface
     */
    protected $resource;
    protected $id;

    public function __construct($id = null)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    public function save()
    {
        return $this->getResource()->save($this);
    }

    public function delete()
    {
        return $this->getResource()->delete($this);
    }

    public function trigger($action)
    {
        return $this->getResource()->trigger($this, $action);
    }

    public function setResource(ResourceInterface $resource)
    {
        if (null !== $this->resource) {
            throw ResourceException::resourceAlreadySet($this);
        }
        $this->resource = $resource;
    }

    public function getResource()
    {
        return $this->resource;
    }

    abstract public function toParameters();

    public function __call($name, $arguments)
    {
        if (method_exists($this, $name)) {
            return call_user_func_array(array($this, $name), $arguments);
        }

        $prop = lcfirst(substr($name, 3));
        if (0 === strpos($name, 'get')) {
            return $this->$prop;
        }
        elseif (0 === strpos($name, 'set')) {
            $this->$prop = $arguments[0];
            return $this;
        }
        throw new \BadMethodCallException(sprintf('Method "%s::%s" does not exist.',
                get_class($this), $name));
    }

}
