<?php

namespace Broda\Component\Rest\Filter\Param;

/**
 * Classe Searching
 *
 * @author raphael
 */
class Searching
{

    protected $value;
    protected $regex = false;
    protected $columnName = null;

    public function __construct($value, $regex = false, $columnName = null)
    {
        $this->value = $value;
        $this->regex = $regex;
        $this->columnName = $columnName;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getRegex()
    {
        return $this->regex;
    }

    public function getColumnName()
    {
        return $this->columnName;
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function setRegex($regex)
    {
        $this->regex = $regex;
        return $this;
    }

    public function setColumnName($columnName)
    {
        $this->columnName = $columnName;
        return $this;
    }


}
