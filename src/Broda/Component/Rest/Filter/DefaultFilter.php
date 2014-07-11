<?php

namespace Broda\Component\Rest\Filter;

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Classe DefaultFilter
 *
 * Usa a query da url ($_GET) para buscar, ordenar e limitar os registros.
 * Ex: "url?s=xyz&limit=20" vai filtrar os registros que contenham em algum lugar "xyz",
 * e irá mostrar só as primeiras 20 linhas.
 *
 *  - Querys usadas:
 *
 *          s: search global
 *     offset: registro inicial
 *      limit: numero de registros a serem mostrados
 *      order: nome da coluna a ser ordenada (só ASC é suportado; pode ser um array para ordenar por mais de uma)
 *   [coluna]: search individual, pela coluna
 *
 * @author raphael
 */
class DefaultFilter extends AbstractFilter
{

    public function __construct($params, array $columns = array())
    {
        if ($params instanceof ParameterBag) {
            $params = $params->all();
        }

        // defining columns
        if (empty($columns)) {
            $columns = static::$defaultColumns;
        }

        foreach ($columns as $col) {
            if (is_string($col)) {
                // simple
                $this->columns[] = new Param\Column($col);
            } else {
                // complete reference
                if ($col instanceof Param\Column) {
                    $this->columns[] = $col;

                } else {
                    $column = new Param\Column($col['name'], $col['data']);
                    $column->setOrderable((bool)$col['orderable']);
                    $column->setSearchable((bool)$col['searchable']);

                    $this->columns[] = $column;
                }
            }
        }

        // limits
        if ($params['offset']) {
            $this->firstResult = (int)$params['offset'];
            unset($params['offset']); // apaga para nao ser confundido com campo no search individual
        }
        if ($params['limit']) {
            $this->maxResults = min(50, (int)$params['limit']);
            unset($params['limit']); // apaga para nao ser confundido com campo no search individual
        }

        // orderings (order array query parameter)
        if ($params['order']) {
            if (is_string($params['order'])) {
                $params['order'] = array($params['order']);
            }

            foreach ($params['order'] as $ord) {
                if ($this->hasColumn($ord)) {
                    $this->orderings = new Param\Ordering($this->getColumn($ord));
                }
            }
            unset($params['order']); // apaga para nao ser confundido com campo no search individual
        }

        // global search is the 's' query parameter
        if (!$this->isEmpty($params['s'])) {
            $this->globalSearch = new Param\Searching($params['s'], false);
            unset($params['s']); // apaga para nao ser confundido com campo no search individual
        }

        // columns search (other query parameters are column searches)
        foreach ($params as $col => $value) {
            if (!$this->isEmpty($value) && $this->hasColumn($col)) {
                $this->columnSearchs[] = new Param\Searching($value, false, $col);
            }
        }
    }

    private function isEmpty($value)
    {
        return empty($value) && !$value == '0';
    }

}
