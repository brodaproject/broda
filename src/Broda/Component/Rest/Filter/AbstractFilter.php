<?php

namespace Broda\Component\Rest\Filter;

use Broda\Component\Rest\Filter\Param\Column;
use Broda\Component\Rest\Filter\Param\Ordering;
use Broda\Component\Rest\Filter\Param\Searching;
use Symfony\Component\HttpFoundation\Request;

/**
 * Classe AbstractFilter
 *
 * Base para outros filters.
 * O programador pode extender desta classe para facilitar ou implementar a interface.
 *
 * Ela contém mais alguns métodos estáticos pra auxiliar, como por exemplo, o
 * detectFilterByRequest(), um factory que retorna o melho filtro dependendo do contexto
 * em que o request foi feito
 *
 * @example
 * function getAllRecords(Request $request)
 * {
 *    AbstractFilter::setDefaultColumns(array('col1', 'col2'));
 *    $filter = AbstractFilter::detectFilterByRequest($request); // detecta filtro correto
 *
 *    $repo = $this->em->getRepository('ModelX'); // ou qualquer Selectable
 *    $filteredRegs = $this->restService->filter($repo, $filter);
 *
 *    return RestResponse($filteredRegs);
 * }
 *
 * @author raphael
 */
abstract class AbstractFilter implements FilterInterface
{

    /**
     *
     * @var Column[]
     */
    protected static $defaultColumns = array();

    /**
     *
     * @var Column[]
     */
    protected $columns = array();

    /**
     *
     * @var Ordering[]
     */
    protected $orderings = array();

    /**
     *
     * @var Searching
     */
    protected $globalSearch;

    /**
     *
     * @var Searching[]
     */
    protected $columnSearchs = array();

    /**
     *
     * @var int
     */
    protected $firstResult = 0;

    /**
     *
     * @var int
     */
    protected $maxResults = 30;

    /**
     * {@inheritdoc}
     */
    public function getColumns()
    {
        return $this->columns;
    }

    public function hasColumn($columnName)
    {
        $filtered = array_filter($this->columns, function ($column) use ($columnName) {
            return $column->getName() === $columnName;
        });
        return (bool)count($filtered);
    }

    public function getColumn($columnName)
    {
        $filtered = array_filter($this->columns, function ($column) use ($columnName) {
            return $column->getName() === $columnName;
        });
        return count($filtered) ? reset($filtered) : null;
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
    public function getFirstResult()
    {
        return $this->firstResult;
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
    public function getMaxResults()
    {
        return $this->maxResults;
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
     * do tipo Column.
     *
     * @param string[]|array[]|Column[] $columns
     */
    public static function normalizeColumns(array $columns)
    {
        $normalizedCols = array();
        foreach ($columns as $col) {
            if (is_string($col)) {
                // simple
                $normalizedCols[] = new Column($col);
            } else {
                // complete reference
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
     * @param Request $request
     * @return FilterInterface|null
     */
    public static function detectFilterByRequest(Request $request)
    {
        if ($request->getMethod() == 'POST') {
            // datatables
            if ($request->request->get('draw')) {
                // 1.10 >=
                return new DataTableFilter($request->request->all());
            } elseif ($request->request->get('sEcho')) {
                // 1.9 legacy or 1.10 em modo de compatibilidade
                return new DataTableLegacyFilter($request->request->all());
            }
        } else {
            if ($request->query->get('limit') && $request->query->get('offset', 1)) {
                // defina as defaultColumns se você vai usar a detecção automatica
                // ou senão o search global e os orders não irão funcionar
                // (só para as colunas definidas como filtros individuais)
                return new BootstrapTableFilter($request->query->all());

            } elseif ($request->query->count()) {
                // defina as defaultColumns se você vai usar a detecção automatica
                // ou senão o search global e os orders não irão funcionar
                // (só para as colunas definidas como filtros individuais)
                return new DefaultFilter($request->query->all());
            }
        }

        return new NullFilter; // impossible to detect
    }
}
