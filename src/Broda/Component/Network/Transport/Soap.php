<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace core\transfer;

/**
 * Classe FileGetContentsTransferAdapter
 *
 * @author Sistema13 <sistema13@furacao.com.br>
 */
class SoapTransferAdapter extends AbstractTransferAdapter
{

    private $soapClient;

    private function open()
    {
        try {
            $context = stream_context_create(array(
                'http' => array(
                    'timeout' => $this->timeout,
                    'header' => //"X-Fw-Token: xxx\r\n".
                                "X-Fw-Transfer: soap\r\n",
                )
            ));

            $parametros = array();
            $parametros['location'] = $this->url;
            $parametros['uri'] = dirname($this->url);
            $parametros['trace'] = 1;
            $parametros['encoding'] = 'iso-8859-1';
            $parametros['exceptions'] = true;
            $parametros['connection_timeout'] = $this->timeout;
            $parametros['stream_context'] = $context;

            if ($this->isProxy()) {
                $parametros['proxy_host'] = $this->proxyHost;
                $parametros['proxy_port'] = $this->proxyPort;

                if ($this->isAuthProxy()) {
                    $parametros['proxy_login'] = $this->proxyLogin;
                    $parametros['proxy_password'] = $this->proxyPass;
                }
            }

            //$parametros['compression'] = SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP; // manda compactado
            // abre uma requisicao para o arquivo, que vai criar as funcoes
            // para serem utilizadas local
            $this->soapClient = new \SoapClient(null, $parametros);
        } catch (\Exception $e) {
            throw TransferException::couldNotConnect($this->url . ($this->isProxy() ? ' (usando proxy)' : ''), $e->getMessage());
        }
    }


    public function connect()
    {
        $this->open();

        try {

            if (!$this->getFunction()) {
                throw TransferException::functionMandatory();
            }

            $response = $this->soapClient->__call($this->getFunction(), $this->getParameters());


            /*$response_headers_str = $this->pointer->__getLastResponseHeaders();
            $response_headers = $this->_readHeaders($response_headers_str);
            if (!$this->checkResponseToken($response_headers['X-FW-Response'])) {
                throw new WrongDataTransferException('Resposta inv�lida do servidor, entre em contato com a Furac�o');
            }*/
        } catch (TransferException $e) {
            throw $e; // se for exception de transfer, lançar do jeito que está
        } catch (\Exception $e) {
            throw TransferException::couldNotTransferData($this->url . ($this->isProxy() ? ' (usando proxy)' : ''), $e->getMessage());
        }

        $this->soapClient = null;

        if (empty($response)) {
            throw TransferException::emptyDataReturned();
        }

        return $response;
    }

}
