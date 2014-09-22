<?php

namespace Broda\Component\Rest\Filter;

/**
 * Classe DataTableFilter
 *
 * TODO: terminar
 *
 * @author raphael
 */
class BootstrapTableFilter extends AbstractFilter implements TotalizableInterface
{

    /**
     *
     * @var array
     */
    protected $params;

    protected $totalRecords = 0;

    public function __construct(array $request, array $columns = array())
    {
        $this->params = $request;

        $this->firstResult = (int)$request['offset'];
        $this->maxResults = min(50, (int)$request['limit'] ?: 30); // max 50 lines per request;

        $this->columns = empty($columns) ? static::$defaultColumns : static::normalizeColumns($columns);

        // bootstrap table only supports global search
        if ($request['search']) {
            $search = $request['search'] ?: '';

            $this->globalSearch = new Param\Searching($search);
        }

        // bootstrap table does not support server ordering
        $this->orderings = array();
    }

    public function setTotalRecords($total, $totalFiltered = null)
    {
        $this->totalRecords = (int)$total;
    }

    public function getTotalFilteredRecords()
    {
        return $this->totalRecords;
    }

    public function getTotalRecords()
    {
        return $this->totalRecords;
    }

    public function getOutputResponse($output)
    {
        return array(
            'total' => $this->totalRecords,
            'rows' => $output
        );
    }

}
