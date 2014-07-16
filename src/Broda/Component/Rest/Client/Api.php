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

    public function getUrl()
    {
        return $this->url;
    }

    public function setUrl($url)
    {
        $this->url = rtrim($url, '/');
        return $this;
    }

    public function createResource($name, $path, $class = 'stdClass', $format = 'json')
    {
        return $this->resources[$name] =
                new ApiResource($this, $name, $path, $class, $format);
    }

    public function getResource($name)
    {
        return $this->resources[$name];
    }

}
