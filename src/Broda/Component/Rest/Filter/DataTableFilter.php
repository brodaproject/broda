<?php

namespace Broda\Component\Rest\Filter;

/**
 * Classe DataTableFilter
 *
 * TODO: implementar ErrorInformableInterface (ver http://datatables.net/manual/server-side)
 *
 * @author raphael
 */
class DataTableFilter extends AbstractFilter implements TotalizableInterface
{

    /**
     *
     * @var array
     */
    protected $params = array();

    protected $totalRecords = 0;

    protected $totalFiltered = 0;

    protected $ajaxSrc = 'data';

    /**
     * Construtor
     *
     * @param array $request
     */
    public function __construct(array $request)
    {
        $this->params = $request;

        if (isset($request['start'])) $this->firstResult = (int)$request['start'];
        if (isset($request['length'])) $this->maxResults = min(50, (int)$request['length'] ?: 30); // max 50 lines per request;

        $columns = empty($request['columns']) ? static::$defaultColumns : static::normalizeColumns($request['columns']);
        $orders = isset($request['order']) ? $request['order'] : array();

        // defining columns and searchings
        $this->columns = $columns;
        if (isset($request['columns'])) {
            foreach ((array)$request['columns'] as $col) {

                if ($col['search']['value']) {
                    $colSearch = new Param\Searching($col['search']['value'], $col['name']);
                    $colSearch->setRegex((bool)$col['search']['regex']);

                    $this->columnSearchs[] = $colSearch;
                }
            }
        }

        // defining search all
        if (isset($request['search']) && isset($request['search']['value'])) {
            $search = $request['search']['value'] ?: '';

            $this->globalSearch = new Param\Searching($search);
            $this->globalSearch->setRegex((bool)$request['search']['regex']);
        }

        // defining orderings
        $i = count($orders);
        while ($i--) {
            $field = $orders[$i]['column'];
            $dir = $orders[$i]['dir'];

            $this->orderings[] = new Param\Ordering($this->columns[$field], $dir);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function setTotalRecords($total, $totalFiltered = null)
    {
        $totalFiltered = isset($totalFiltered) ? $totalFiltered : $total;
        $this->totalRecords = (int)$total;
        $this->totalFiltered = (int)$totalFiltered;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalRecords()
    {
        return $this->totalRecords;
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalFilteredRecords()
    {
        return $this->totalFiltered;
    }

    /**
     * {@inheritdoc}
     *
     * Nota: Retorna "self" em vez de "static" porque é melhor criar um objeto
     * já correto do que um novo "legacy" tendo que normalizar de novo o array de
     * request.
     */
    public function createFilterForTotalRecords()
    {
        $newFilter = clone $this;
        $newFilter->clearSearchs();
        $newFilter->clearLimits();
        return $newFilter;
    }

    /**
     * {@inheritdoc}
     *
     * Nota: Retorna "self" em vez de "static" porque é melhor criar um objeto
     * já correto do que um novo "legacy" tendo que normalizar de novo o array de
     * request.
     */
    public function createFilterForTotalFilteredRecords()
    {
        $newFilter = clone $this;
        $newFilter->clearLimits();
        return $newFilter;
    }

    /**
     * Define o key do ajaxSrc do DataTable.
     *
     * @param $ajaxSrc
     */
    public function setAjaxSrc($ajaxSrc)
    {
        $this->ajaxSrc = $ajaxSrc;
    }

    /**
     * {@inheritdoc}
     */
    public function getOutputResponse($output)
    {
        $a = $this->ajaxSrc;
        return array(
            'draw' => (int)$this->params['draw'],
            'recordsTotal' => $this->totalRecords,
            'recordsFiltered' => $this->totalFiltered,
            $a => $output
        );
    }

}
