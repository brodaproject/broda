<?php

namespace Broda\Core\Controller;


interface InjectorInterface
{
    /**
     * Cria uma instância da classe com os serviços injetados.
     *
     * Este método vai ler o __construct e os métodos setters da
     * classe para saber quais serviços deve injetar nela.
     *
     * @param string $class Nome da classe a ser instanciada
     * @return mixed Instância da classe
     */
    public function createInstance($class);
} 