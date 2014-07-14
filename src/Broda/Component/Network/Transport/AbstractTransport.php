<?php

namespace Broda\Component\Network\Transport;

/**
 * Classe AbstractTransferAdapter
 *
 */
abstract class AbstractTransport implements TransportInterface
{
    protected $url;
    protected $parameters = array();
    protected $functionName;
    protected $timeout = 10;
    protected $proxyHost;
    protected $proxyPort;
    protected $proxyLogin; // se tiver, proxy requer autenticacao
    protected $proxyPass;
    protected $proxyProtocol; // só para CURL

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function setParameter($key, $value)
    {
        $this->parameters[$key] = $value;
    }

    public function getParameter($key)
    {
        return $this->parameters[$key];
    }

    public function setParameters(array $params)
    {
        $this->parameters = array();
        $this->addParameters($params);
    }

    public function getParameters()
    {
        return $this->parameters;
    }

    public function addParameters(array $params)
    {
        $this->parameters = array_merge($this->parameters, $params);
    }

    public function setFunction($functionName)
    {
        $this->functionName = $functionName;
    }

    public function getFunction()
    {
        return $this->functionName;
    }

    public function setProxy($host, $port, $login = null, $pass = null, $protocol = null)
    {
        $this->proxyHost = $host;
        $this->proxyPort = $port;
        $this->proxyLogin = $login;
        $this->proxyPass = $pass;
        $this->proxyProtocol = $protocol;
    }

    public function isProxy()
    {
        return !empty($this->proxyHost);
    }

    public function isAuthProxy()
    {
        return !empty($this->proxyLogin);
    }

    protected function serializeParams(array $params)
    {
        $serialized = array();
        foreach ($params as $key => $val) {
            if (is_array($val)) {
                foreach ($val as $key2 => $val2) {
                    $serialized[] = $key . '[' . $key2 . ']=' . $val2;
                }
            }
            else {
                $serialized[] = "$key=$val";
            }
        }
        return implode('&', $serialized);
    }

    private function parseStringHeadersItem($header)
    {
        list($title, $content) = explode(':', $header);
        return array(trim($title) => trim($content));
    }

    protected function parseStringHeaders($stringHeaders)
    {
        $headers = explode("\n", $stringHeaders);
        $newHeaders = array();
        foreach ($headers as $header) {
            if ($header) {
                $newHeaders += $this->parseStringHeadersItem($header);
            }
        }
        return $newHeaders;
    }
}
