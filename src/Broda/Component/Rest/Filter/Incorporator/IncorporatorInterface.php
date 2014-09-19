<?php

namespace Broda\Component\Rest\Filter\Incorporator;

use Broda\Component\Rest\Filter\FilterInterface;

/**
 * Interface IncorporatorInterface
 *
 * @author raphael
 */
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

    /**
     * Filtra uma coleção de dados e retorna uma nova coleção filtrados
     * pelo {@link FilterInterface}.
     *
     * @param mixed           $collection Coleção/repositório de dados a serem filtrados
     * @param FilterInterface $filter     Filtro definido pelo usuário via client side
     * @return mixed Coleção dos dados filtrados
     */
    public function incorporate($collection, FilterInterface $filter);

    /**
     * Retorna a contagem do total de registros filtrados pelo
     * {@link FilterInterface} na coleção de dados.
     *
     * @param mixed           $collection Coleção/repositório de dados a serem filtrados
     * @param FilterInterface $filter     Filtro definido pelo usuário via client side
     * @return int Total de registros filtrados
     */
    public function count($collection, FilterInterface $filter);

    /**
     * Retorna TRUE se a coleção é suportada pelo Incorporator.
     *
     * @param mixed $collection Coleção/repositório de dados a serem filtrados
     * @return bool
     */
    public static function supports($collection);

} 