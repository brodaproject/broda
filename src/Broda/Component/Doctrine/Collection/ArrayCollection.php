<?php

namespace Broda\Component\Doctrine\Collection;

use Broda\Component\Doctrine\Expr\NotStrictClosureExpressionVisitor;
use Doctrine\Common\Collections\ArrayCollection as BaseArrayCollection;
use Doctrine\Common\Collections\Criteria;

/**
 * Classe ArrayCollection
 *
 * @author raphael
 */
class ArrayCollection extends BaseArrayCollection
{
    /**
     * {@inheritDoc}
     */
    public function matching(Criteria $criteria)
    {
        $expr     = $criteria->getWhereExpression();
        $filtered = $this->toArray();

        if ($expr) {
            $visitor  = new NotStrictClosureExpressionVisitor();
            $filter   = $visitor->dispatch($expr);
            $filtered = array_filter($filtered, $filter);
        }

        if ($orderings = $criteria->getOrderings()) {
            $next = null;
            foreach (array_reverse($orderings) as $field => $ordering) {
                $next = NotStrictClosureExpressionVisitor::sortByField($field, $ordering == 'DESC' ? -1 : 1, $next);
            }

            usort($filtered, $next);
        }

        $offset = $criteria->getFirstResult();
        $length = $criteria->getMaxResults();

        if ($offset || $length) {
            $filtered = array_slice($filtered, (int)$offset, $length);
        }

        return new static($filtered);
    }
}
