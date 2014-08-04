<?php

namespace Broda\Component\Rest\Filter;

/**
 * Classe DefaultFilter
 *
 * Usa a query da url ($_GET) para buscar, ordenar e limitar os registros.
 * Ex: "url?s=xyz&len=20" vai filtrar os registros que contenham em algum lugar "xyz",
 * e irá mostrar só as primeiras 20 linhas.
 *
 *  - Querys usadas:
 *
 *          s: search global
 *      start: registro inicial
 *        len: numero de registros a serem mostrados
 *      order: nome da coluna a ser ordenada (só ASC é suportado; pode ser um array para ordenar por mais de uma)
 *   [coluna]: search individual, pela coluna
 *
 * @author raphael
 */
class DefaultFilter extends AbstractFilter
{

    public function __construct(array $params, array $columns = array())
    {
        // defining columns
        $this->columns = empty($columns) ? static::$defaultColumns : static::normalizeColumns($columns);

        // limits
        if (isset($params['start'])) {
            $this->firstResult = (int)$params['start'];
        }
        if (isset($params['len'])) {
            $this->maxResults = min(50, (int)$params['len']);
        }

        // orderings (order array query parameter)
        if (isset($params['order'])) {
            if (is_string($params['order'])) {
                $params['order'] = array($params['order']);
            }

            foreach ($params['order'] as $ord) {
                if ($this->hasColumn($ord)) {
                    $this->orderings[] = new Param\Ordering($this->getColumn($ord));
                }
            }
        }

        // global search is the 's' query parameter
        if (isset($params['s']) && !$this->isEmpty($params['s'])) {
            $this->globalSearch = new Param\Searching($params['s'], false);
        }

        // apaga para nao ser confundido com campo no search individual
        unset($params['start'], $params['len'], $params['order'], $params['s']);

        // columns search (other query parameters are column searches)
        foreach ($params as $col => $value) {
            // FIXME: pensar se este if realmente é legal ter aqui
            if (!$this->hasColumn($col)) {
                $this->columns[] = new Param\Column($col);
            }
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
