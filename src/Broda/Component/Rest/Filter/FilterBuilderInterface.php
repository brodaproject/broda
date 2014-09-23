<?php

namespace Broda\Component\Rest\Filter;


interface FilterBuilderInterface
{

    /**
     * Retorna o filtro construido.
     *
     * @return FilterInterface
     */
    public function getFilter();

} 