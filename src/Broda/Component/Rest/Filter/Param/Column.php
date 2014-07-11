<?php

namespace Broda\Component\Rest\Filter\Param;

/**
 * Classe Column
 *
 * @author raphael
 */
class Column
{
    protected $name;

    protected $data;

    protected $orderable = true;

    protected $searchable = true;

    public function __construct($name, $data = null)
    {
        $this->name = $name;
        $this->data = $data ?: $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getData()
    {
        return $this->data;
    }

    public function getOrderable()
    {
        return $this->orderable;
    }

    public function getSearchable()
    {
        return $this->searchable;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function setOrderable($orderable)
    {
        $this->orderable = $orderable;
        return $this;
    }

    public function setSearchable($searchable)
    {
        $this->searchable = $searchable;
        return $this;
    }


}
