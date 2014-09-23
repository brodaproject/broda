<?php

namespace Broda\Component\Rest\Filter;

use Broda\Component\Rest\Filter\Param\Column;
use Broda\Component\Rest\Filter\Param\Ordering;
use Broda\Component\Rest\Filter\Param\Searching;

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

    /**
     * Criador.
     *
     * @return self
     */
    public static function create()
    {
        return new static();
    }

    /**
     * Adiciona uma coluna.
     *
     * @param string $name       Nome da coluna. Geralmente é o nome do campo no banco de dados
     * @param string $data       Nome alternativo. Geralmente é o nome da coluna no json/xml
     * @param bool   $orderable  Se a coluna é ordenável ou não
     * @param bool   $searchable Se a coluna é pesquisável ou não pelo global search
     * @return self
     * @throws \InvalidArgumentException Se o nome da coluna for inválido
     */
    public function addColumn($name, $data = null, $orderable = true, $searchable = true)
    {
        $this->validateCol($name);
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
     * Adiciona uma subcoluna.
     *
     * As subcolunas são usadas pelos searchs para pesquisarem também por
     * elas quando usadas na pesquisa.
     *
     * Mais informações em {@link Param\Column}
     *
     * @param string $columnName Nome da coluna pai.
     * @param string $name       Nome da coluna. Geralmente é o nome do campo no banco de dados
     * @param string $data       Nome alternativo. Geralmente é o nome da coluna no json/xml
     * @param bool   $orderable  Se a coluna é ordenável ou não
     * @param bool   $searchable Se a coluna é pesquisável ou não pelo global search
     * @return self
     * @throws \InvalidArgumentException Se o nome da coluna for inválido
     * @throws \LogicException           Se a coluna pai não existir
     */
    public function addSubColumn($columnName, $name, $data = null, $orderable = true, $searchable = true)
    {
        if (!isset($this->columns[$columnName])) {
            throw new \LogicException(sprintf('Coluna %s não existe. Defina-a primeiro.', $columnName));
        }
        $this->validateCol($name);
        $this->columns[$columnName]['subcolumns'][] = array(
            'name' => $name,
            'data' => $data,
            'orderable' => $orderable,
            'searchable' => $searchable,
        );
        return $this;
    }

    /**
     * Define as colunas.
     *
     * Deve ser um array no seguinte formato:
     * [
     *     0: {
     *         name: ...,
     *         data: ...,
     *         ordenable: ...,
     *         searchable: ...,
     *         subcolumns: [ ... ]
     *     },
     *     ...
     * ]
     *
     * @param array $columns
     * @return self
     * @throws \InvalidArgumentException Se o nome da coluna for inválido
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
                isset($col['data']) ? $col['data'] : null,
                isset($col['orderable']) ? $col['orderable'] : true,
                isset($col['searchable']) ? $col['searchable'] : true
            );
            if (isset($col['subcolumns'])) {
                foreach ($col['subcolumns'] as $subcol) {
                    $this->addSubColumn(
                        $col['name'],
                        $subcol['name'],
                        isset($subcol['data']) ? $subcol['data'] : null,
                        isset($subcol['orderable']) ? $subcol['orderable'] : true,
                        isset($subcol['searchable']) ? $subcol['searchable'] : true
                    );
                }
            }
        }
        return $this;
    }

    /**
     * Adiciona uma pesquisa individual por coluna.
     *
     * @param string $name   Nome da coluna.
     * @param string $search Pesquisa.
     * @return self
     * @throws \InvalidArgumentException Se a pesquisa estiver vazia
     * @throws \InvalidArgumentException Se o nome da coluna for inválido
     */
    public function addColumnSearch($name, $search)
    {
        if (empty($search)) {
            throw new \InvalidArgumentException('O valor a ser pesquisado não pode ser vazio.');
        }

        $this->validateCol($name);
        $this->columnSearchs[] = array(
            'name' => $name,
            'searchValue' => $search,
        );

        if (!isset($this->columns[$name])) {
            $this->addColumn($name);
        }
        return $this;
    }

    /**
     * Define as pesquisas individuais para colunas.
     *
     * Deve ser um array no seguinte formato:
     * [
     *     0: {
     *         name: ...,
     *         searchValue: ...,
     *     },
     *     ...
     * ]
     *
     * Ou:
     *
     * {
     *     name: searchValue,
     *     ...
     * }
     *
     * @param array $searchs
     * @return self
     * @throws \InvalidArgumentException Se o nome da coluna for inválido
     */
    public function setColumnSearchs(array $searchs)
    {
        if (!is_numeric(key($searchs))) {
            // arruma array se vier no formato (nome => pesquisa, ...)
            $tmp = array();
            foreach ($searchs as $name => $search) {
                $tmp[] = array('name' => $name, 'searchValue' => $search);
            }
            $searchs = $tmp;
        }

        $filtered = array_filter($searchs, function ($elem) {
            return is_array($elem) && isset($elem['name'])
                && (!empty($elem['searchValue']) || !empty($elem['search']));
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
     * Define o index do primeiro registro a ser mostrado (offset).
     *
     * @param int $firstResult
     * @return self
     */
    public function setFirstResult($firstResult)
    {
        $this->firstResult = $firstResult;
        return $this;
    }

    /**
     * Define o número de registros a serem mostrados (limit).
     *
     * @param int $maxResults
     * @return self
     */
    public function setMaxResults($maxResults)
    {
        $this->maxResults = $maxResults;
        return $this;
    }

    /**
     * Define uma pesquisa global (em todas as colunas pesquisáveis).
     *
     * @param string $globalSearch
     * @return self
     */
    public function setGlobalSearch($globalSearch)
    {
        $this->globalSearch = $globalSearch;
        return $this;
    }

    /**
     * Adiciona uma ordenação.
     *
     * @param string $name      Noma da coluna.
     * @param int    $direction Direção. Use 1 para 'ASC' e -1 para 'DESC'
     * @return self
     * @throws \InvalidArgumentException Se o nome da coluna for inválido
     */
    public function addOrdering($name, $direction = 1)
    {
        $this->validateCol($name);
        $this->orders[] = array(
            'name' => $name,
            'dir' => $direction,
        );

        if (!isset($this->columns[$name])) {
            $this->addColumn($name);
        }

        return $this;
    }

    /**
     * Define as ordenações.
     *
     * Deve ser um array no seguinte formato:
     * [
     *     0: {
     *         name: ...,
     *         dir: ...,
     *     },
     *     ...
     * ]
     *
     * Ou:
     *
     * {
     *     name: dir,
     *     ...
     * }
     *
     * @param array $orderings
     * @return self
     * @throws \InvalidArgumentException Se o nome da coluna for inválido
     */
    public function setOrderings(array $orderings)
    {
        if (!is_numeric(key($orderings))) {
            // arruma array se vier no formato (nome => dir, ...)
            $tmp = array();
            foreach ($orderings as $name => $dir) {
                $tmp[] = array('name' => $name, 'dir' => $dir);
            }
            $orderings = $tmp;
        }

        $filtered = array_filter($orderings, function ($elem) {
            return is_array($elem) && isset($elem['name']);
        });

        $this->orders = array();
        foreach ($filtered as $col) {
            $this->addOrdering(
                $col['name'],
                isset($col['dir']) ? $col['dir'] : 1
            );
        }
        return $this;
    }

    /**
     * Define se o filter terá informações do total filtrado e
     * total de registros.
     *
     * @param bool $bool
     * @return self
     */
    public function setTotalizable($bool)
    {
        $this->isTotalizable = (bool)$bool;
        return $this;
    }

    /**
     * Define o callback do output deste filtro.
     *
     * @param callable $callback
     * @return self
     */
    public function setOutputCallback($callback)
    {
        $this->validateCallback($callback);
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
            return new Searching($colData['searchValue'], $colData['name']);
        }, $this->columnSearchs);

        $filter->setColumns($columns);
        $filter->setOrderings($orderings);
        $filter->setColumnSearchs($columnSearchs);

        return $filter;
    }

    /**
     * @internal Valida se o nome da coluna atende os padrões
     */
    private function validateCol($name)
    {
        if (empty($name) || is_numeric($name)) {
            throw new \InvalidArgumentException(sprintf('O nome da coluna deve ser uma string não vazia e não numérica, %s dado.', gettype($name)));
        }
    }

    /**
     * @internal Valida se o callback é um callable válido
     */
    private function validateCallback($callback)
    {
        if (!is_callable($callback)) {
            throw new \InvalidArgumentException(sprintf('O callback de output deve ser um callable, %s dado.', gettype($callback)));
        }
    }

} 