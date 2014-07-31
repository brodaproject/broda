<?php

namespace Broda\Tests\Component\Rest\Mocks;

use Broda\Component\Rest\Filter\AbstractFilter;

/**
 * Classe AbstractFilterMock
 *
 * @author raphael
 */
class AbstractFilterMock extends AbstractFilter
{

    public function __construct()
    {
        $this->columns = self::$defaultColumns;
    }

    function setColumns(array $columns)
    {
        $this->columns = $columns;
    }

    function setColumnSearchs(array $columns)
    {
        $this->columnSearchs = $columns;
    }

    function setFirstResult($num)
    {
        $this->firstResult = $num;
    }

    function setMaxResults($num)
    {
        $this->maxResults = $num;
    }

    function setGlobalSearch($search)
    {
        $this->globalSearch = $search;
    }

    function setOrderings(array $orders)
    {
        $this->orderings = $orders;
    }

}
