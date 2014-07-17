<?php

namespace Broda\Component\Rest\Annotation\Server;

/**
 * Extensão para definição de um resource
 *
 * @Annotation
 * @Target({"CLASS"})
 *
 * @author raphael
 */
class Resource
{

    private $basePath;
    private $idName = 'id';
    private $format;
    private $parent;
    private $service;

    /**
     * Constructor.
     *
     * @param array $data An array of key/value parameters.
     *
     * @throws \BadMethodCallException
     */
    public function __construct(array $data)
    {
        if (isset($data['value'])) {
            $data['base_path'] = $data['value'];
            unset($data['value']);
        }

        foreach ($data as $key => $value) {
            $method = 'set'.str_replace('_', '', $key);
            if (!method_exists($this, $method)) {
                throw new \BadMethodCallException(sprintf("Unknown property '%s' on annotation '%s'.", $key, get_class($this)));
            }
            $this->$method($value);
        }
    }

    public function getBasePath()
    {
        return $this->basePath;
    }

    public function getIdName()
    {
        return $this->idName;
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function setBasePath($basePath)
    {
        $this->basePath = '/'.trim($basePath, '/');
    }

    public function setIdName($idName)
    {
        $this->idName = $idName;
    }

    public function setFormat($format)
    {
        $this->format = $format;
    }

    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    public function getService()
    {
        return $this->service;
    }

    public function setService($service)
    {
        $this->service = $service;
    }




}
