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
class SocketTransferAdapter extends AbstractTransferAdapter
{

    private $sck;

    private $host;
    private $uri;

    private function normalizeUrl()
    {
        $this->host = str_replace('http://', '', $this->url);

        $parts = explode('/', $this->host, 2);
        $this->host = $parts[0];
        $this->uri = '/'.$parts[1];
    }

    private function open()
    {
        $errno = $errstr = null;
        if ($this->isProxy()) {
            $this->sck = fsockopen($this->proxyHost, $this->proxyPort, $errno, $errstr, $this->timeout);
        } else {
            $this->sck = fsockopen($this->host, 80, $errno, $errstr, $this->timeout);
        }

        if (!$this->sck) {
            throw TransferException::couldNotConnect($this->url . ($this->isProxy() ? ' (usando proxy)' : ''), "[$errno] $errstr");
        }
    }

    private function close()
    {
        fclose($this->sck);
    }

    public function connect()
    {
        $this->normalizeUrl();
        $this->open();

        $sendData = $this->serializeParams($this->getParameters());

        $headers = array();
        if ($this->isProxy()) {
            //$headers[] = "POST $this->url HTTP/1.1";
            $headers[] = "GET $this->url?$sendData HTTP/1.1";
            $headers[] = "Host: $this->proxyHost";
            if ($this->isAuthProxy()) {
                $headers[] = "Proxy-Authorization: Basic " . base64_encode("$this->proxyLogin:$this->proxyPass");
            }
        }
        else {
            //$headers[] = "POST $this->uri HTTP/1.1";
            $headers[] = "GET $this->uri?$sendData HTTP/1.1";
            $headers[] = "Host: $this->host";
        }
        //$headers[] = "X-FW-Token: " . $this->getToken();
        $headers[] = "X-FW-Transfer: socket";
        $headers[] = "Content-Type: application/x-www-form-urlencoded";
        $headers[] = "Content-Length: " . strlen($sendData);
        $headers[] = "Connection: close";
        $headers[] = ""; // enter necessario
        $headers[] = $sendData; // dados a serem passados

        $header_data = implode("\r\n", $headers);

        // manda no stream o header com os dados
        fwrite($this->sck, $header_data);

        // le primeiro o header de response
        do {
            $responseHeader.= fread($this->sck, 1);
        } while (!preg_match('/\\r\\n\\r\\n$/', $responseHeader));

        // depois le o conteudo do response
        if (!strstr($responseHeader, "Transfer-Encoding: chunked")) {
            // se nao veio chunkado, ler tudo
            $response = '';
            while (!feof($this->sck)) {
                $response .= fgets($this->sck, 128);
            }
        }
        else {
            // veio chunkado, ler os pedaços
            $response = '';
            while ($chunk_length = hexdec(fgets($this->sck))) {
                $response_chunk = '';

                $read_length = 0;

                while ($read_length < $chunk_length) {
                    $response_chunk .= fread($this->sck, $chunk_length - $read_length);
                    $read_length = strlen($response_chunk);
                }
                $response .= $response_chunk;

                fgets($this->sck);
            }
        }
        $response = chop($response);

        $this->close();

        if (empty($response)) {
            throw TransferException::emptyDataReturned();
        }

        return $response;
    }

}
