<?php

namespace Broda\Component\Rest\Filter;

/**
 * Os filtros que informam para o usuário o erro que ocorreu
 * ao processar os dados devem implementar essa interface.
 *
 * TODO: criar uma espécie de "enabled" para desabilitar qdo necessário
 *
 * @author raphael
 */
interface ErrorInformableInterface extends FilterInterface
{

    /**
     * Define a mensagem de erro para ser informada ao usuário.
     *
     * @param string $message
     */
    public function setErrorMessage($message);

    /**
     * Retorna a mensagem de erro que será informada ao usuário.
     *
     * @return string
     */
    public function getErrorMessage();

} 