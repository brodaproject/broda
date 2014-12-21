<?php

namespace Broda\Component\Rest\Filter;
use Broda\Component\Rest\Filter\Param\Column;
use Broda\Component\Rest\Filter\Param\Ordering;
use Broda\Component\Rest\Filter\Param\Searching;

/**
 * Filters são classes que fazem uma filtragem básica dos registros para serem
 * retornados do REST. Ele irá definir os parâmetros para uma filtragem simples
 * (limitar registros, ordernar, busca por campo ou global), para que o RestService
 * possa usar para pós-filtrar os registros que deseja.
 *
 * É útil para plugins como DataTables, por exemplo, para que os registros sejam
 * páginados, ordenados e buscados por ele sem o programador ter que se preocupar com
 * essa filtragem básica.
 *
 * @example
 * // com EntityRepository
 * function getAllRecords(Request $request)
 * {
 *    $filter = new DataTableFilter($request->request->all());
 *
 *    $repo = $this->em->getRepository('ModelX');
 *    $filteredRegs = $this->restService->filter($repo, $filter);
 *
 *    return RestResponse($filteredRegs); // já retorna no formato correto exigido pelo DataTables
 * }
 *
 * // com Selectable
 * function getAllRecords(Request $request)
 * {
 *    $filter = new DataTableFilter($request->request->all());
 *
 *    $data = new ArrayCollection(require '/path/to/data.json'); // ou array comum
 *    $filteredRegs = $this->restService->filter($data, $filter);
 *
 *    return RestResponse($filteredRegs);
 * }
 *
 * @author raphael
 */
interface FilterInterface
{

    /**
     * @return Param\Column[]
     */
    public function getColumns();

    /**
     * Retorna a coluna.
     *
     * @param $columnName
     * @return Param\Column
     */
    public function getColumn($columnName);

    /**
     * @param $columnName
     * @return boolean
     */
    public function hasColumn($columnName);

    /**
     * @return Param\Ordering[]
     */
    public function getOrderings();

    /**
     * @return int
     */
    public function getFirstResult();

    /**
     * @return int
     */
    public function getMaxResults();

    /**
     * @return Param\Searching
     */
    public function getGlobalSearch();

    /**
     * @return Param\Searching[]
     */
    public function getColumnSearchs();

    /**
     * Define as colunas de parametro do filtro
     *
     * @param Column[] $columns
     * @return self
     */
    public function setColumns(array $columns);

    /**
     * TODO: doc
     *
     * @param Searching[] $columnSearchs
     * @return self
     */
    public function setColumnSearchs(array $columnSearchs);

    /**
     * TODO: doc
     *
     * @param int $firstResult
     * @return self
     */
    public function setFirstResult($firstResult);

    /**
     * TODO: doc
     *
     * @param Searching $globalSearch
     * @return self
     */
    public function setGlobalSearch(Searching $globalSearch);

    /**
     * TODO: doc
     *
     * @param int $maxResults
     * @return self
     */
    public function setMaxResults($maxResults);

    /**
     * TODO: doc
     *
     * @param Ordering[] $orderings
     * @return self
     */
    public function setOrderings(array $orderings);

    /**
     *
     * @return self
     */
    public function clearSearchs();

    /**
     * @return self
     */
    public function clearLimits();

    /**
     * Retorna o response da forma que o filtro do lado cliente entenda.
     *
     * Geralmente, apenas retorna o mesmo resultado que encontrar, mas tem casos,
     * por exemplo, o datatables, que exige um array com alguns elementos como 'draw'
     * e 'recordsTotal'.
     *
     * @param mixed $output Response
     * @return mixed
     */
    public function getOutputResponse($output);


}
