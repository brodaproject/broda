<?php

namespace Broda\Component\Rest\Filter;

/**
 * Classe DataTableFilter
 *
 * @author raphael
 */
class DataTableFilter extends AbstractFilter
{

    /**
     *
     * @var array
     */
    protected $params = array();

    protected $totalRecords = 0;

    protected $totalFiltered = 0;

    protected $ajaxSrc = 'data';

    public function __construct(array $request)
    {
        $this->params = $request;

        $this->firstResult = (int)$request['start'];
        $this->maxResults = min(50, (int)$request['length'] ?: 30); // max 50 lines per request;

        $columns = empty($request['columns']) ? static::$defaultColumns : static::normalizeColumns($request['columns']);
        $orders = $request['order'] ?: array();

        // defining columns and searchings
        $this->columns = $columns;
        foreach ($request['columns'] as $col) {

            if ($col['search']['value']) {
                $colSearch = new Param\Searching($col['search']['value'],
                        (bool)$col['search']['regex'], $col['name']);

                $this->columnSearchs[] = $colSearch;
            }
        }

        // defining search all
        if ($request['search']['value']) {
            $search = $request['search']['value'] ?: '';

            $this->globalSearch = new Param\Searching($search, false);
        }

        // defining orderings
        $i = count($orders);
        while ($i--) {
            $field = $orders[$i]['column'];
            $dir = $orders[$i]['dir'];

            $this->orderings[] = new Param\Ordering($this->columns[$field], $dir);
        }
    }

    public function setTotalRecords($total)
    {
        $this->totalRecords = (int)$total;
        $this->totalFiltered = (int)$total;
    }

    public function setTotalFilteredRecords($totalFiltered)
    {
        $this->totalFiltered = (int)$totalFiltered;
    }

    public function setAjaxSrc($ajaxSrc)
    {
        $this->ajaxSrc = $ajaxSrc;
    }

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
