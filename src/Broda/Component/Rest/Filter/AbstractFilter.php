<?php

namespace Broda\Component\Rest\Filter;

use Broda\Component\Rest\Filter\Param\Column;
use Broda\Component\Rest\Filter\Param\Ordering;
use Broda\Component\Rest\Filter\Param\Searching;
use Symfony\Component\HttpFoundation\Request;

/**
 * Base para outros filters.
 * O programador pode extender desta classe para facilitar ou implementar a interface.
 *
 * Ela contém mais alguns métodos estáticos pra auxiliar, como por exemplo, o
 * detectFilterByRequest(), um factory que retorna o melho filtro dependendo do contexto
 * em que o request foi feito
 *
 * @example
 * function getAllRecords()
 * {
 *    AbstractFilter::setDefaultColumns(array('col1', 'col2'));
 *    $filter = AbstractFilter::detectFilterByRequest($_REQUEST); // detecta filtro correto
 *
 *    $repo = $this->em->getRepository('ModelX'); // ou qualquer Selectable
 *    $filteredRegs = $this->restService->filter($repo, $filter);
 *
 *    return RestResponse($filteredRegs);
 * }
 *
 * TODO: arrumar doc
 *
 * @author raphael
 */
abstract class AbstractFilter implements FilterInterface
{

    /**
     * @var Column[]
     */
    protected static $defaultColumns = array();

    /**
     * @var Column[]
     */
    protected $columns = array();

    /**
     * @var Ordering[]
     */
    protected $orderings = array();

    /**
     * @var Searching
     */
    protected $globalSearch;

    /**
     * @var Searching[]
     */
    protected $columnSearchs = array();

    /**
     * @var int
     */
    protected $firstResult = 0;

    /**
     * @var int
     */
    protected $maxResults = null;

    /**
     * {@inheritdoc}
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * {@inheritdoc}
     */
    public function hasColumn($columnName)
    {
        $filtered = array_filter($this->columns, function (Column $column) use ($columnName) {
            return $column->getName() === $columnName;
        });
        return (bool)count($filtered);
    }

    /**
     * {@inheritdoc}
     */
    public function getColumn($columnName)
    {
        $filtered = array_filter($this->columns, function (Column $column) use ($columnName) {
            return $column->getName() === $columnName;
        });
        return count($filtered) ? reset($filtered) : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setColumns(array $columns)
    {
        $onlyColumns = array_filter($columns, function ($elem) {
            return ($elem instanceof Column);
        });
        $this->columns = array_values($onlyColumns);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getColumnSearchs()
    {
        return $this->columnSearchs;
    }

    /**
     * {@inheritdoc}
     */
    public function setColumnSearchs(array $columnSearchs)
    {
        $onlyColumns = array_filter($columnSearchs, function ($elem) {
            // somente searchs que possuem nome de coluna
            return ($elem instanceof Searching) && null !== $elem->getColumnName();
        });
        $this->columnSearchs = array_values($onlyColumns);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFirstResult()
    {
        return $this->firstResult;
    }

    /**
     * {@inheritdoc}
     */
    public function setFirstResult($firstResult)
    {
        $this->firstResult = null === $firstResult ? $firstResult : (int)$firstResult;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getGlobalSearch()
    {
        return $this->globalSearch;
    }

    /**
     * {@inheritdoc}
     */
    public function setGlobalSearch(Searching $globalSearch)
    {
        $this->globalSearch = $globalSearch;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxResults()
    {
        return $this->maxResults;
    }

    /**
     * {@inheritdoc}
     */
    public function setMaxResults($maxResults)
    {
        $this->maxResults = null === $maxResults ? $maxResults : (int)$maxResults;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrderings()
    {
        return $this->orderings;
    }

    /**
     * {@inheritdoc}
     */
    public function setOrderings(array $orderings)
    {
        $onlyOrderings = array_filter($orderings, function ($elem) {
            return ($elem instanceof Ordering);
        });
        $this->orderings = array_values($onlyOrderings);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearSearchs()
    {
        $this->columnSearchs = array();
        $this->globalSearch = null;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function clearLimits()
    {
        $this->firstResult = 0;
        $this->maxResults = null;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getOutputResponse($output)
    {
        return $output;
    }

    /**
     * Define as colunas padrão para todos os filtros.
     *
     * Útil para a detecção automática de filtro, quando você não sabe de que contexto
     * url será chamada.
     *
     * @param string[]|array[]|Column[] $columns
     */
    public static function setDefaultColumns(array $columns)
    {
        static::$defaultColumns = static::normalizeColumns($columns);
    }

    /**
     * Normaliza as colunas passadas por parametro e transforma em objetos
     * do tipo {@link Column}.
     *
     * @param string[]|array|Column[] $columns
     * @return Column[]
     */
    public static function normalizeColumns(array $columns)
    {
        $normalizedCols = array();
        foreach ($columns as $col) {
            if (is_string($col)) {
                // simples
                $normalizedCols[] = new Column($col);
            } else {
                // referencia completa
                if ($col instanceof Column) {
                    $normalizedCols[] = $col;
                    continue;
                }
                $column = new Column($col['name'], isset($col['data']) ? $col['data'] : null);
                if (isset($col['orderable'])) $column->setOrderable((bool)$col['orderable']);
                if (isset($col['searchable'])) $column->setSearchable((bool)$col['searchable']);
                if (isset($col['subcolumns'])) {
                    $column->setSubColumns(self::normalizeColumns((array)$col['subcolumns']));
                }

                $normalizedCols[] = $column;
            }
        }
        return $normalizedCols;
    }

    /**
     * Detecta o filtro mais adequado para o tipo de request que veio
     *
     * @param array|Request $request $_REQUEST, $_POST ou $_GET, ou um objeto Request
     * @return FilterInterface|null
     */
    public static function detectFilterByRequest($request)
    {
        // suporte para o Request do HttpFoundation
        if (class_exists('Symfony\Component\HttpFoundation\Request', false)
            && $request instanceof Request) {
            $request = array_merge($request->query->all(), $request->request->all());
        }

        if (isset($request['draw'])) {
            // datatables 1.10 >=
            return new DataTableFilter($request);
        } elseif (DataTableLegacyFilter::isDataTableLegacy($request)) {
            // datatables 1.9 legacy ou 1.10 em modo de compatibilidade
            return new DataTableLegacyFilter($request);
        } elseif (isset($request['dynatable'])
            || (isset($request['page']) && isset($request['perPage']))
            || (count($request['queries']) || count($request['sorts']))
        ) {
            // dynatable
            // defina as defaultColumns se você vai usar a detecção automatica
            // ou senão o search global e os orders não irão funcionar
            return new DynatableFilter($request);
        } elseif (count($request)) {
            // defina as defaultColumns se você vai usar a detecção automatica
            // ou senão o search global e os orders não irão funcionar
            // (só para as colunas definidas como filtros individuais)
            return new BasicFilter($request);
        }

        return new NullFilter; // impossível de detectar
    }
}
