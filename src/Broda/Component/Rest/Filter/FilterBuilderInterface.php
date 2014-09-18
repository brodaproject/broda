<?php

namespace Broda\Component\Rest\Filter;

use Doctrine\Common\Collections\Criteria;

interface FilterBuilderInterface
{

    public function addColumn($name, $data = null, $orderable = true, $searchable = true);

    public function setColumns(array $columns);

    public function setColumnSearchs(array $searchs);

    public function addColumnSearch($name, $search);

    public function setFirstResult($firstResult);

    public function setMaxResults($maxResults);

    public function setGlobalSearch($globalSearch);

    public function addOrdering($name, $direction = Criteria::ASC);

    public function setOrderings(array $orderings);

    /**
     * @return FilterInterface
     */
    public function getFilter();

} 