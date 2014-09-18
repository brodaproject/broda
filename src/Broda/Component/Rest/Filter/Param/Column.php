<?php

namespace Broda\Component\Rest\Filter\Param;

/**
 * Representa uma coluna e suas propriedades no pós-filtro.
 *
 * O nome da coluna deve ser único e geralmente representa
 * o nome do campo da tabela no banco de dados.
 *
 * @author raphael
 */
class Column
{

    protected $name;
    protected $data;
    protected $orderable = true;
    protected $searchable = true;

    /**
     * Sub-colunas para ser possível fazer busca múltipla em uma busca individual.
     *
     * Só afeta buscas individuais. Busca global ignora as sub-colunas.
     *
     * @var Column[]
     */
    protected $subColumns = array();

    public function __construct($name, $data = null)
    {
        $this->name = $name;
        $this->data = $data ? : $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    public function getOrderable()
    {
        return $this->orderable;
    }

    public function setOrderable($orderable)
    {
        $this->orderable = $orderable;
        return $this;
    }

    public function getSearchable()
    {
        return $this->searchable;
    }

    public function setSearchable($searchable)
    {
        $this->searchable = $searchable;
        return $this;
    }

    public function addSubColumn(Column $column)
    {
        $this->subColumns[] = $column;
        return $this;
    }

    public function removeSubColumn(Column $column)
    {
        $this->subColumns = array_values(array_filter($this->subColumns,
                function ($elm) use ($column) {
            return $elm->getName() !== $column->getName();
        }));
        return $this;
    }

    public function setSubColumns(array $subColumns)
    {
        if (count($subColumns) && !($col = reset($subColumns)) instanceof Column) {
            throw new \InvalidArgumentException(sprintf('Only %s are acceptable, '
                    . '%s given', get_class($this), gettype($col)));
        }
        $this->subColumns = $subColumns;
        return $this;
    }

    public function getSubColumns()
    {
        return $this->subColumns;
    }

}
