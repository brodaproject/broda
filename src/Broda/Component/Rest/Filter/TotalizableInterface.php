<?php

namespace Broda\Component\Rest\Filter;

/**
 * Todas os filtros que retornam ou precisam do total de registros devem
 * implementar esta interface.
 *
 * TODO melhorar essa doc
 *
 * @author raphael
 */
interface TotalizableInterface extends FilterInterface
{

    /**
     * Define o total de registros sem as limitações de paginação
     * e sem qualquer filtragem definida pelo cliente.
     *
     * Opcionalmente, pode ser passado o total de registros
     * com a filtragem definida pelo cliente como segundo
     * parametro. Se não informado, o valor será o mesmo do total
     * de registros sem paginação/filtragem cliente.
     *
     * O {@link RestService} automaticamente preenche este parametro
     * pelo método {@link RestService::filter}.
     *
     * @param int $total         Total de registros sem limitadores/filtragem do cliente
     * @param int $totalFiltered Total de registros sem limitadores, mas com filtragem de cliente
     */
    public function setTotalRecords($total, $totalFiltered = null);

    /**
     * Retorna o total de registros sem as limitações de paginação
     * e sem qualquer filtragem definida pelo cliente.
     *
     * @return int
     */
    public function getTotalRecords();

    /**
     * Retorna o total de registros sem as limitações de paginação,
     * mas com a filtragem definia pelo cliente.
     *
     * @return int
     */
    public function getTotalFilteredRecords();

    /**
     * Retorna um novo {@link FilterInterface} sem os limitadores de
     * paginação e sem filtragem definida pelo cliente.
     *
     * @return FilterInterface
     */
    public function createFilterForTotalRecords();

    /**
     * Retorna um novo {@link FilterInterface} sem os limitadores de
     * paginação, mas com a filtragem definida pelo cliente.
     *
     * @return FilterInterface
     */
    public function createFilterForTotalFilteredRecords();
}
