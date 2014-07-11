<?php

namespace Broda\Component\Rest\Filter;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Classe DataTableFilter
 *
 * @author raphael
 */
class BootstrapTableFilter extends AbstractFilter
{

    /**
     *
     * @var ParameterBag
     */
    protected $params;

    protected $totalRecords = 0;

    public function __construct(ParameterBag $request, array $columns = array())
    {
        $this->params = $request;

        $this->firstResult = (int)$request->get('offset');
        $this->maxResults = min(50, (int)$request->get('limit', 30)); // max 50 lines per request;

        if (empty($columns)) {
            $columns = static::$defaultColumns;
        }

        foreach ($columns as $col) {
            if (is_string($col)) {
                // simple
                $this->columns[] = new Param\Column($col);
            } else {
                // complete reference
                if ($col instanceof Param\Column) {
                    $this->columns[] = $col;

                } else {
                    $column = new Param\Column($col['name'], $col['data']);
                    $column->setOrderable((bool)$col['orderable']);
                    $column->setSearchable((bool)$col['searchable']);

                    $this->columns[] = $column;
                }
            }
        }

        // bootstrap table only supports global search
        if ($request->get('search', null)) {
            $search = $request->get('search', '');

            $this->globalSearch = new Param\Searching($search, false);
        }

        // bootstrap table does not support server ordering
        $this->orderings = array();
    }

    public function setTotalRecords($total)
    {
        $this->totalRecords = (int)$total;
    }

    public function getOutputResponse($output)
    {
        return array(
            'total' => $this->totalRecords,
            'rows' => $output
        );
    }

}
