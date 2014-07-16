<?php

namespace Broda\Component\Rest\Client;

/**
 * Classe ApiResource
 *
 * @see ApiResourceInterface
 * @author raphael
 */
class ApiResource implements ApiResourceInterface
{
    /**
     *
     * @var Api
     */
    private $api;

    private $class;

    private $path;

    private $basePath;

    private $format;

    private $name;

    private $params = array();

    public function __construct(Api $api, $name, $class, $path, $format)
    {
        $this->api = $api;
        $this->name = $name.
        $this->class = $class;
        $this->setPath($path);
        $this->format = $format;
    }

    /*public function createResource($name, $path, $class = 'stdClass', $format = 'json')
    {
        // TODO fazer
        // o complicado dos subresources é que o id do resource pai vai conflitar com
        // o resource filho.
        // possível solução: colocar o parametro $name como primeiro argumento
        // deste método na assinatura da interface e controlar as instancias de
        // dentro da Api, chamando por nome. quando for chamar um subresource, usar notação
        // por ponto (.)
        // ex: $api->getResource('people.article');
        return $this->api->createResource($this->name . '.' . $name, $path, $class, $format);
    }*/

    public function getApi()
    {
        return $this->api;
    }

    public function getClass()
    {
        return $this->class;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getFormat()
    {
        return $this->format;
    }

    public function setApi(Api $api)
    {
        $this->api = $api;
        return $this;
    }

    public function setClass($class = 'stdClass')
    {
        $this->class = $class;
        return $this;
    }

    public function setFormat($format = 'json')
    {
        $this->format = $format;
        return $this;
    }

    public function setPath($path)
    {
        // TODO separar o {id}
        $path = '/'. trim($path, '/');
        list($uri, $querystring) = explode('?', $path, 2);

        // define params
        $params = array();
        parse_str($querystring, $params);
        $this->params = $params;

        // define parts
        list($base, $format) = explode('{id}', $uri, 2);
        $this->basePath = '/'. trim($base, '/');
        $this->format = $format;

        $this->path = $path;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getParameters()
    {
        return $this->params;
    }

    public function getParameter($key)
    {
        return $this->params[$key];
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setParameters(array $params)
    {
        $this->params = $params;
        return $this;
    }

    public function setParameter($key, $value)
    {
        $this->params[$key] = $value;
        return $this;
    }


    public function getCurrentPath($id = null, $action = null, array $append = array())
    {
        return $this->api->getUrl() .
                $this->basePath .
                (null !== $id ? '/'.$id : '') .
                (null !== $action ? '/'.$action : '') .
                $this->getQueryString($append);
    }

    private function getQueryString(array $append = array())
    {
        $qs = '';

        if ($this->params) {
            $qs .= '?'. http_build_query($this->params);
        }
        if ($append) {
            $qs .= ($qs ? '&' : '?') . http_build_query($append);
        }
        return $qs;
    }

}
