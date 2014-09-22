<?php

namespace Broda\Component\Rest\Filter;


use Broda\Component\Rest\Filter\Param\Column;
use Broda\Component\Rest\Filter\Param\Ordering;
use Broda\Component\Rest\Filter\Param\Searching;
use Doctrine\Common\Collections\Criteria;

class FilterBuilder implements FilterBuilderInterface
{

    private $columns = array();
    private $columnSearchs = array();
    private $firstResult;
    private $maxResults;
    private $globalSearch;
    private $orders = array();
    private $isTotalizable = false;
    private $outputCallback;

    public static function create()
    {
        return new static();
    }

    /**
     * {@inheritdoc}
     */
    public function addColumn($name, $data = null, $orderable = true, $searchable = true)
    {
        $this->columns[$name] = array(
            'name' => $name,
            'data' => $data,
            'orderable' => $orderable,
            'searchable' => $searchable,
            'subcolumns' => array(),
        );
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addSubColumn($columnName, $name, $data = null, $orderable = true, $searchable = true)
    {
        if (!isset($this->columns[$columnName])) {
            throw new \UnexpectedValueException(sprintf('Coluna %s não existe. Defina primeiro.', $columnName));
        }
        $this->columns[$columnName]['subcolumns'][] = array(
            'name' => $name,
            'data' => $data,
            'orderable' => $orderable,
            'searchable' => $searchable,
        );
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setColumns(array $columns)
    {
        $filtered = array_filter($columns, function ($elem) {
            return is_array($elem) && isset($elem['name']);
        });

        $this->columns = array();
        foreach ($filtered as $col) {
            $this->addColumn(
                $col['name'],
                $col['data'],
                $col['orderable'],
                $col['searchable']
            );
            if (isset($col['subcolumns'])) {
                foreach ($col['subcolumns'] as $subcol) {
                    $this->addColumn(
                        $col['name'],
                        $subcol['name'],
                        $subcol['data'],
                        $subcol['orderable'],
                        $subcol['searchable']
                    );
                }
            }
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addColumnSearch($name, $search)
    {
        $this->columnSearchs[] = array(
            'name' => $name,
            'searchValue' => $search,
        );
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * TODO: permitir o formato (nome => pesquisa, ...)
     */
    public function setColumnSearchs(array $searchs)
    {
        $filtered = array_filter($searchs, function ($elem) {
            return is_array($elem) && isset($elem['name']);
        });

        $this->columnSearchs = array();
        foreach ($filtered as $col) {
            $this->addColumnSearch(
                $col['name'],
                $col['searchValue'] ?: $col['search']
            );
        }
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setFirstResult($firstResult)
    {
        $this->firstResult = $firstResult;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setMaxResults($maxResults)
    {
        $this->maxResults = $maxResults;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setGlobalSearch($globalSearch)
    {
        $this->globalSearch = $globalSearch;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function addOrdering($name, $direction = Criteria::ASC)
    {
        $this->orders[] = array(
            'name' => $name,
            'dir' => $direction,
        );
        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * TODO: permitir o formato (nome => dir, ...)
     */
    public function setOrderings(array $orderings)
    {
        //TODO
    }

    /**
     * {@inheritdoc}
     */
    public function setTotalizable($bool)
    {
        $this->isTotalizable = (bool)$bool;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setOutputCallback($callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException(sprintf('A callable is required, %s given', gettype($callback)));
        }
        $this->outputCallback = $callback;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilter()
    {
        if ($this->isTotalizable) {
            $filter = new GenericTotalizableFilter();
        } else {
            $filter = new GenericFilter();
        }

        if (isset($this->outputCallback)) $filter->setOutputCallback($this->outputCallback);
        if (isset($this->firstResult)) $filter->setFirstResult($this->firstResult);
        if (isset($this->maxResults)) $filter->setMaxResults($this->maxResults);
        if (isset($this->globalSearch)) $filter->setGlobalSearch(new Searching($this->globalSearch));

        $columns = array_map(function ($colData) {
            $col = new Column($colData['name'], $colData['data']);
            $col->setOrderable($colData['orderable']);
            $col->setSearchable($colData['searchable']);
            $col->setSubColumns(array_map(function ($subcolData) {
                $subcol = new Column($subcolData['name'], $subcolData['data']);
                $subcol->setOrderable($subcolData['orderable']);
                $subcol->setSearchable($subcolData['searchable']);
                return $subcol;
            }, $colData['subcolumns']));

            return $col;
        }, $this->columns);

        $orderings = array_map(function ($colData) use ($columns) {
            return new Ordering($columns[$colData['name']], $colData['dir']);
        }, $this->orders);

        $columnSearchs = array_map(function ($colData) {
            return new Searching($colData['searchValue'], true, $colData['name']);
        }, $this->columnSearchs);

        $filter->setColumns($columns);
        $filter->setOrderings($orderings);
        $filter->setColumnSearchs($columnSearchs);

        return $filter;
    }

} 