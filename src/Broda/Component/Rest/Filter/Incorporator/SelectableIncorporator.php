<?php

namespace Broda\Component\Rest\Filter\Incorporator;

use Broda\Component\Rest\Filter\Param as FilterParam;
use Broda\Component\Rest\Filter\FilterInterface;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use Doctrine\Common\Collections\Selectable;

class SelectableIncorporator implements IncorporatorInterface
{
    /**
     * {@inheritdoc}
     */
    public function incorporate($collection, FilterInterface $filter)
    {
        /* @var $collection Selectable */
        $criteria = $this->getFilteringCriteria($filter);
        return $collection->matching($criteria);
    }

    /**
     * {@inheritdoc}
     */
    public function count($collection, FilterInterface $filter)
    {
        /* @var $collection Selectable */
        $criteria = $this->getFilteringCriteria($filter);
        return $collection->matching($criteria)->count();
    }

    /**
     * {@inheritdoc}
     */
    public static function supports($collection)
    {
        return is_object($collection) && ($collection instanceof Selectable);
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
     * @internal
     * @param FilterParam\Searching[] $columnSearchs
     * @param FilterInterface $filter
     * @return CompositeExpression
     */
    private function getExpressionForColumnSearchs(array $columnSearchs, FilterInterface $filter)
    {
        $searchExprs = array();
        foreach ($columnSearchs as $col) {
            /* @var $col FilterParam\Searching */
            $field = $col->getColumnName();

            $searchs = $col->isTokenizable()
                ? $col->getTokens()
                : (array)$col->getValue();

            $searchColExprs = array();
            foreach ($searchs as $search) {

                $compExpr = $col->isExactly()
                    ? Criteria::expr()->eq($field, $search)
                    : Criteria::expr()->contains($field, $search);

                $column = $filter->getColumn($field);
                if ($column && count($subcols = $column->getSubColumns())) {
                    // with subcolumns

                    $searchSubColExprs = array();
                    $searchSubColExprs[] = $compExpr; // self col ...
                    foreach ($subcols as $subcol) {
                        /* @var $subcol FilterParam\Column */
                        $subfield = $subcol->getName();

                        $compSubExpr = $col->isExactly()
                            ? Criteria::expr()->eq($subfield, $search)
                            : Criteria::expr()->contains($subfield, $search);

                        $searchSubColExprs[] = $compSubExpr; // .. and his subcolumns
                    }

                    $searchColExprs[] = $this->createCompositeExpression(
                        CompositeExpression::TYPE_OR, $searchSubColExprs);
                } else {
                    // no subcolumns
                    $searchColExprs[] = $compExpr;
                }
            }

            $searchExprs[] = $this->createCompositeExpression(
                $col->getTokenSeparator(), $searchColExprs);

        }
        return $this->createCompositeExpression(
            CompositeExpression::TYPE_AND, $searchExprs);
    }

    /**
     * @internal
     * @param FilterParam\Searching $globalSearch
     * @param FilterInterface $filter
     * @return CompositeExpression
     */
    private function getExpressionForGlobalSearch(FilterParam\Searching $globalSearch,
                                                  FilterInterface $filter)
    {
        $searchAllExprs = array();
        foreach ($filter->getColumns() as $col) {
            if (!$col->getSearchable()) continue;

            $field = $col->getName();

            $searchs = $globalSearch->isTokenizable()
                ? $globalSearch->getTokens()
                : (array)$globalSearch->getValue();

            $searchColExprs = array();
            foreach ($searchs as $search) {
                $compExpr = $globalSearch->isExactly()
                    ? Criteria::expr()->eq($field, $search)
                    : Criteria::expr()->contains($field, $search);

                $searchColExprs[] = $compExpr;
            }

            $searchAllExprs[] = $this->createCompositeExpression(
                $globalSearch->getTokenSeparator(), $searchColExprs);
        }
        return $this->createCompositeExpression(
            CompositeExpression::TYPE_OR, $searchAllExprs);
    }

    /**
     * @internal
     * @param FilterParam\Ordering[] $orders
     * @param FilterInterface $filter
     * @return array
     */
    private function getOrderings(array $orders, FilterInterface $filter)
    {
        $orderings = array();
        foreach ($orders as $order) {
            /* @var $order FilterParam\Ordering */
            $field = $order->getColumn()->getName();
            $dir = $order->getDir();

            if (!$order->getColumn()->getOrderable()) continue;

            $orderings[$field] = $dir;
        }
        return $orderings;
    }

    /**
     * Retorna um {@link Criteria} para um {@link FilterInterface}.
     *
     * @param FilterInterface $filter
     * @return Criteria
     */
    public function getFilteringCriteria(FilterInterface $filter)
    {
        $criteria = Criteria::create();

        $columnSearchs = $filter->getColumnSearchs();
        $globalSearch = $filter->getGlobalSearch();
        $orders = $filter->getOrderings();
        $start = $filter->getFirstResult();
        $length = $filter->getMaxResults();

        // constroi as expressões e os orderings
        $orderings = $this->getOrderings($orders, $filter);
        $searchExpr = $this->getExpressionForColumnSearchs($columnSearchs, $filter);
        $searchAllExpr = (null !== $globalSearch)
            ? $this->getExpressionForGlobalSearch($globalSearch, $filter)
            : null;

        // monta o criteria
        if (null !== $searchAllExpr) $criteria->andWhere($searchAllExpr);
        if (null !== $searchExpr) $criteria->andWhere($searchExpr);
        if (count($orderings)) $criteria->orderBy($orderings);
        $criteria->setFirstResult($start);
        $criteria->setMaxResults($length);

        return $criteria;
    }

} 