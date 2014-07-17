<?php

namespace Broda\Component\Rest\Client;

/**
 * Classe Api
 *
 * @author raphael
 */
class Api implements ApiResourceCreatorInterface
{

    private $url;
    private $resources = array();

    public function __construct($url)
    {
        $this->setUrl($url);
    }

    /**
     * Retorna o url absoluto do api
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Define um url para o api
     *
     * O valor deve ser sempre uma url absoluta.
     * ex: http://api.ex.com/api/v1/
     *
     * @param type $url
     * @return Api
     */
    public function setUrl($url)
    {
        $this->url = rtrim($url, '/');
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function createResource($name, $path, $class = 'stdClass', $format = 'json')
    {
        return $this->resources[$name] =
                new ApiResource($this, $name, $path, $class, $format);
    }

    /**
     * {@inheritDoc}
     */
    public function getResource($name)
    {
        if (!isset($this->resources[$name])) {
            throw new \RuntimeException(sprintf('ApiResource "%s" não existe. '
                    . 'Crie-o primeiro com createResource()', $name));
        }
        return $this->resources[$name];
    }

}
