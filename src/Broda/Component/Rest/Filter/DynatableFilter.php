<?php

namespace Broda\Component\Rest\Filter;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Filter para o Dynatable
 *
 * @link http://www.dynatable.com/
 * @author raphael
 */
class DynatableFilter extends AbstractFilter implements TotalizableInterface
{

    protected $totalRecords = 0;

    protected $totalFiltered = 0;

    public function __construct(array $request, array $columns = array())
    {
        if (isset($request['offset'])) {
            $this->firstResult = (int)$request['offset'];
        }
        if (isset($request['perPage'])) {
            // maximo de 50 linhas
            $this->maxResults = min(50, (int)$request['perPage'] ?: 30);
        }

        $columns = empty($columns)
            ? static::$defaultColumns
            : static::normalizeColumns($columns);

        // definindo global searchs e column searchs
        $this->columns = $columns;
        if (isset($request['queries'])) {
            foreach ((array)$request['queries'] as $type => $value) {
                if ($value) {
                    if ($type === 'search') {
                        // global search
                        $this->globalSearch = new Param\Searching($value);
                    } else {
                        // column search
                        $this->columnSearchs[] = new Param\Searching($value, $type);
                    }
                }
            }
        }

        // definindo ordenações
        foreach ((array)$request['sorts'] as $ord => $dir) {
            if ($this->hasColumn($ord)) {
                $this->orderings[] = new Param\Ordering($this->getColumn($ord), $dir);
            }
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
     */
    public function createFilterForTotalFilteredRecords()
    {
        $newFilter = clone $this;
        $newFilter->clearLimits();
        return $newFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function getOutputResponse($output)
    {
        return array(
            'totalRecordCount' => $this->totalRecords,
            'queryRecordCount' => $this->totalFiltered,
            'records' => $output
        );
    }

}
