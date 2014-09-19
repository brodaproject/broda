<?php

namespace Broda\Component\Rest\Filter;


class GenericTotalizableFilter extends GenericFilter implements TotalizableInterface
{
    protected $totalRecords = 0;

    protected $totalFiltered = 0;

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

} 