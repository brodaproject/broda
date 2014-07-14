<?php

namespace Broda\Component\Network\Transport;

/**
 * Interface TransferAdapterInterface
 *
 * @author Sistema13 <sistema13@furacao.com.br>
 */
interface TransportInterface
{
    public function connect();

    public function setUrl($url);
    public function getUrl();

    public function setParameter($key, $value);
    public function getParameter($key);
    public function setParameters(array $params);
    public function getParameters();
    public function addParameters(array $params);

    public function setFunction($functionName);
    public function getFunction();

    public function isProxy();
    public function isAuthProxy();
    public function setProxy($host, $port, $login = null, $pass = null, $protocol = null);

}
