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

    public function __construct(Api $api, $name, $path, $class, $format)
    {
        $this->api = $api;
        $this->name = $name;
        $this->class = $class;
        $this->setPath($path);
        $this->format = $format;
    }

    /**
     * {@inheritDoc}
     */
    public function getApi()
    {
        return $this->api;
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * {@inheritDoc}
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * {@inheritDoc}
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * {@inheritDoc}
     */
    public function setApi(Api $api)
    {
        $this->api = $api;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setClass($class = 'stdClass')
    {
        $this->class = $class;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setFormat($format = 'json')
    {
        $this->format = $format;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setPath($path)
    {
        // TODO separar o {id}
        $path = '/' . trim($path, '/');
        list($uri, $querystring) = explode('?', $path, 2);

        // define params
        $params = array();
        parse_str($querystring, $params);
        $this->params = $params;

        // define parts
        list($base, $format) = explode('{id}', $uri, 2);
        $this->basePath = '/' . trim($base, '/');
        $this->format = $format;

        $this->path = $path;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getParameters()
    {
        return $this->params;
    }

    /**
     * {@inheritDoc}
     */
    public function getParameter($key)
    {
        return $this->params[$key];
    }

    /**
     * {@inheritDoc}
     */
    public function setParameters(array $params)
    {
        $this->params = $params;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setParameter($key, $value)
    {
        $this->params[$key] = $value;
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCurrentPath($id = null, $action = null, array $append = array())
    {
        return $this->api->getUrl() .
                $this->basePath .
                (null !== $id ? '/' . $id . (null !== $action ? '/' . $action : '') : '') .
                (null !== $this->format ? '.' . $this->format : '') .
                $this->getQueryString($append);
    }

    /**
     * Retorna a query string formatada para o resource
     *
     * @param array $append
     * @return string
     */
    private function getQueryString(array $append = array())
    {
        $qs = '';

        if ($this->params) {
            $qs .= '?' . http_build_query($this->params);
        }
        if ($append) {
            $qs .= ($qs ? '&' : '?') . http_build_query($append);
        }
        return $qs;
    }

}
