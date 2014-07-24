<?php

namespace Broda\Component\Rest\Filter;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Classe DynatableFilter
 *
 * TODO: terminar e entender melhor como é esse dynatable
 * TODO: criar uma classe abstrata de plugins de tables
 *
 * @author raphael
 */
class DynatableFilter extends AbstractFilter
{

    /**
     *
     * @var array
     */
    protected $params;

    protected $totalRecords = 0;

    protected $totalFiltered = 0;

    protected $ajaxSrc = 'records';

    public function __construct(array $request, array $columns = array())
    {
        $this->params = $request;

        $this->firstResult = (int)$request['offset'];
        $this->maxResults = min(50, (int)$request['perPage'] ?: 30); // max 50 lines per request;

        $this->columns = empty($columns) ? static::$defaultColumns : static::normalizeColumns($columns);
        // FIZ ATÉ AQUI, CONTINUAR

        $orders = $request->get('sorts', array());

        // defining columns and searchings
        foreach ($columns as $col) {
            $column = new Param\Column($col['name'], $col['data']);
            $column->setSearchable((bool)$col['searchable']);
            $column->setOrderable((bool)$col['orderable']);

            $this->columns[] = $column;

            if ($col['search']['value']) {
                $colSearch = new Param\Searching($col['search']['value'],
                        (bool)$col['search']['regex'], $col['name']);

                $this->columnSearchs[] = $colSearch;
            }
        }

        // defining search all
        if ($request->get('search[value]', null, true)) {
            $search = $request->get('search[value]', '', true);

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
            'totalRecordCount' => $this->totalRecords,
            'queryRecordCount' => $this->totalFiltered,
            $a => $output
        );
    }

}
