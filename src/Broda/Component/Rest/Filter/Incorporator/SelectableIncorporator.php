<?php

namespace Broda\Component\Rest\Filter\Incorporator;

use Broda\Component\Rest\Filter\Param as FilterParam;
use Broda\Component\Rest\Filter\FilterInterface;
use Broda\Component\Rest\Filter\TotalizableInterface;
use Broda\Component\Rest\RestService;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use Doctrine\Common\Collections\Selectable;

class SelectableIncorporator extends AbstractIncorporator
{
    public function incorporate($object, FilterInterface $filter)
    {
        /* @var $object Selectable */
        $criteria = $this->getFilteringCriteria($filter);

        if ($filter instanceof TotalizableInterface) {

            switch ($totalizableMode = $this->rest->getTotalizableMode()) {
                case RestService::TOTALIZABLE_ALL:
                case RestService::TOTALIZABLE_ONLY_FILTERED:

                    $totalCriteria = $this->getFilteringCriteria($filter->createFilterForTotalFilteredRecords());
                    $totalCollection = $object->matching($totalCriteria);

                    $totalFiltered = $totalCollection->count();

                    if ($totalizableMode === RestService::TOTALIZABLE_ALL) {
                        // faz mais uma busca para trazer o total sem filtro
                        $totalCriteria = $this->getFilteringCriteria($filter->createFilterForTotalRecords());
                        $totalCollection = $object->matching($totalCriteria);

                        $total = $totalCollection->count();
                    } else {
                        $total = $totalFiltered;
                    }

                    $filter->setTotalRecords($total, $totalFiltered);
                    unset($totalCollection);
                    break;
                case RestService::TOTALIZABLE_UNKNOWN:
                    $filter->setTotalRecords(
                        $filter->getFirstResult() + $filter->getMaxResults() + 1
                    );
                    break;
            }

        }

        return $object->matching($criteria);
    }

    /**
     * @internal
     *
     * @param $type
     * @param array $exprs
     * @return CompositeExpression|null
     */
    private function createCompositeExpression($type, array $exprs)
    {
        switch (count($exprs)) {
            case 0:
                return null;
            case 1:
                return $exprs[0];
            default:
                foreach ($exprs as $expr) {
                    if (null === $expr) return null;
                }
                return new CompositeExpression($type, $exprs);
        }
    }

    /**
     * Retorna o/um Criteria com os pósfiltros do FilterInterface.
     *
     * Útil para
     *
     * @param FilterInterface $filter
     * @return Criteria
     */
    public function getFilteringCriteria(FilterInterface $filter)
    {
        $criteria = Criteria::create();
        $expr = Criteria::expr();

        $columns = $filter->getColumns();
        $columnSearchs = $filter->getColumnSearchs();
        $globalSearch = $filter->getGlobalSearch();
        $orders = $filter->getOrderings();
        $start = $filter->getFirstResult();
        $length = $filter->getMaxResults(); // max 50 lines per request

        // defining search especific columns
        $searchExprs = array();
        foreach ($columnSearchs as $col) {
            /* @var $col FilterParam\Searching */
            $field = $col->getColumnName();

            $searchColExprs = array();
            foreach ($col->getTokens() as $search) {

                $column = $filter->getColumn($field);
                if ($column && count($subcols = $column->getSubColumns())) {
                    // with subcolumns

                    $searchSubColExprs = array();
                    $searchSubColExprs[] = $expr->contains($field, $search); // self col ...
                    foreach ($subcols as $subcol) {
                        /* @var $subcol FilterParam\Column */
                        $subfield = $subcol->getName();
                        $searchSubColExprs[] = $expr->contains($subfield, $search); // .. and his subcolumns
                    }

                    $searchColExprs[] = $this->createCompositeExpression(CompositeExpression::TYPE_OR, $searchSubColExprs);
                } else {
                    // no subcolumns
                    $searchColExprs[] = $expr->contains($field, $search);
                }
            }

            $searchExprs[] = $this->createCompositeExpression(CompositeExpression::TYPE_AND /* TODO: deixar essa opção customizavel*/, $searchColExprs);

        }
        $searchExpr = $this->createCompositeExpression(CompositeExpression::TYPE_AND, $searchExprs);

        // defining search all
        $searchAllExprs = array();
        if (null !== $globalSearch) {

            foreach ($columns as $col) {
                /* @var $col FilterParam\Column */
                $field = $col->getName();

                if (!$col->getSearchable()) continue;

                $searchColExprs = array();
                foreach ($globalSearch->getTokens() as $search) {
                    $searchColExprs[] = $expr->contains($field, $search);
                }

                $searchAllExprs[] = $this->createCompositeExpression(CompositeExpression::TYPE_AND /* TODO: deixar essa opção customizavel*/, $searchColExprs);
            }
        }
        $searchAllExpr = $this->createCompositeExpression(CompositeExpression::TYPE_OR, $searchAllExprs);

        // defining orderings
        $orderings = array();
        foreach ($orders as $order) {
            /* @var $order FilterParam\Ordering */
            $field = $order->getColumn()->getName();
            $dir = $order->getDir();

            if (!$order->getColumn()->getOrderable()) continue;

            $orderings[$field] = $dir;
        }

        // mount criteria
        if (null !== $searchAllExpr) $criteria->andWhere($searchAllExpr);
        if (null !== $searchExpr) $criteria->andWhere($searchExpr);
        if (count($orderings)) $criteria->orderBy($orderings);
        $criteria->setFirstResult($start);
        $criteria->setMaxResults($length);

        return $criteria;
    }

} 