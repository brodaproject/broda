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
class CUrlTransferAdapter extends AbstractTransferAdapter
{

    private $curl;

    private function open()
    {
        $this->curl = curl_init();
        if (!$this->curl) {
            throw TransferException::couldNotConnect($this->url . ($this->isProxy() ? ' (usando proxy)' : ''), 'Desconhecido');
        }

        curl_setopt($this->curl, CURLOPT_FAILONERROR, true); // erro >= 400 dá falha
        curl_setopt($this->curl, CURLOPT_FRESH_CONNECT, true); // nunca usar cache
        curl_setopt($this->curl, CURLOPT_HEADER, true);  // para os readers de response virem junto com o body
        /*curl_setopt($this->curl, CURLOPT_HTTPHEADER,
                array('X-FW-Token: ' . $this->getToken()));*/
        curl_setopt($this->curl, CURLOPT_HTTPHEADER,
                array('X-FW-Transfer: curl'));
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true); // retorna response em string em vez de output
        if ($this->isProxy()) {
            curl_setopt($this->curl, CURLOPT_PROXY, $this->proxyHost);
            curl_setopt($this->curl, CURLOPT_PROXYPORT, $this->proxyPort);
            switch ($this->proxyProtocol) {
                case 'http':
                    curl_setopt($this->curl, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                    break;
                case 'socks4':
                    curl_setopt($this->curl, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4);
                    break;
                case 'socks5':
                    curl_setopt($this->curl, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
                    break;
            }

            if ($this->isAuthProxy()) {
                curl_setopt($this->curl, CURLOPT_PROXYAUTH, CURLAUTH_BASIC);
                curl_setopt($this->curl, CURLOPT_PROXYUSERPWD,
                        "$this->proxyLogin:$this->proxyPass");
            }
        }
    }

    private function close()
    {
        curl_close($this->curl);
    }

    public function connect()
    {
        $this->open();

        $sendData = $this->serializeParams($this->getParameters());

        curl_setopt($this->curl, CURLOPT_URL, "$this->url?$sendData");
        //curl_setopt($this->curl, CURLOPT_POST, true); // HTTP POST
        //curl_setopt($this->curl, CURLOPT_POSTFIELDS, $sendData);

        $response = curl_exec($this->curl);

        if ($response === false) {
            $errno = @curl_errno($this->curl);
            $error = @curl_strerror($errno);
            $this->close();
            throw TransferException::couldNotTransferData($this->url . ($this->isProxy() ? ' (usando proxy)' : ''), "[$errno] $error");
        }

        list($response_headers_str, $response) = explode("\r\n\r\n", $response);

        /*$response_headers = $this->_readHeaders($response_headers_str);
        if (!$this->checkResponseToken($response_headers['X-FW-Response'])) {
            throw new WrongDataTransferException('Resposta inv�lida do servidor, entre em contato com a Furac�o');
        }*/
        $this->close();

        if (empty($response)) {
            throw TransferException::emptyDataReturned();
        }

        return $response;
    }

}
