<?php

namespace Broda\Framework\Model;

/**
 * Interface para facilitar os models que podem ser deletados mas precisam ser mantidos
 * no banco de dados apenas com uma flag setada como TRUE (1)
 *
 * @author raphael
 */
interface DeletableInterface
{

    /**
     * Deve retornar o nome do campo da tabela que serve como flag
     * para indicar se o registro está excluido ou não.
     *
     * @return string Nome do campo (SQL) na tabela
     */
    public static function getDeletableField();
}
