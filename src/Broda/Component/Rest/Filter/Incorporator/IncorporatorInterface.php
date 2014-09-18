<?php

namespace Broda\Component\Rest\Filter\Incorporator;


use Broda\Component\Rest\Filter\FilterInterface;

interface IncorporatorInterface
{

    /**
     * Neste modo, os Totalizables irão fazer 2 SELECTs: um para retornar
     * o total de registros sem filtragem e sem limitação de paginação,
     * e outro para retornar o total de registros sem limitação, com filtragem.
     *
     * É um modo mais lento, evite se possível.
     */
    const TOTALIZABLE_ALL = 100;

    /**
     * Neste modo, os Totalizables irão fazer apenas 1 SELECT, para
     * retornar o total de registros sem limitação de paginação, mas
     * com filtragem, e o total sem filtragem será substituido por este.
     *
     * É o padrão, porém alguns plugins como DataTables não irá
     * funcionar o 'totalFiltered'.
     */
    const TOTALIZABLE_ONLY_FILTERED = 101;

    /**
     * Neste modo, os Totalizables não farão nenhum SELECT a mais,
     * e retornarão uma previsão de quantos registros tem na tabela
     * baseados nas limitações de paginação.
     *
     * Use quando não interessa mostrar o total de registros ou
     * quando a tabela conter muitos registros.
     */
    const TOTALIZABLE_UNKNOWN = 102;

    public function incorporate($object, FilterInterface $filter);

    public function setFieldMap(array $fieldMap);
} 