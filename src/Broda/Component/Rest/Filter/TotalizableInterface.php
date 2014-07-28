<?php

namespace Broda\Component\Rest\Filter;

/**
 * Interface TotalizableInterface
 *
 * Todas os filtros que retornam ou precisam do total de registros devem
 * implementar esta interface.
 *
 * @author raphael
 */
interface TotalizableInterface
{

    public function setTotalRecords($total, $totalFiltered = null);

    public function getTotalRecords();

    public function getTotalFilteredRecords();
}
