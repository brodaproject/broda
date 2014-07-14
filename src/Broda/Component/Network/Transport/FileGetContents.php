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
class FileGetContentsTransferAdapter extends AbstractTransferAdapter
{

    public function connect()
    {
        $sendData = $this->serializeParams($this->getParameters());

        $context = stream_context_create(array(
            'http' => array(
                'timeout' => $this->timeout,
                'header' => //"X-Fw-Token: xxx\r\n".
                            "X-Fw-Transfer: file\r\n",
            )
        ));

        $response = @file_get_contents("$this->url?$sendData", false, $context);

        if ($response === false) {
            throw TransferException::unknownError();
        }

        if (empty($response)) {
            throw TransferException::emptyDataReturned();
        }

        return $response;
    }

}
