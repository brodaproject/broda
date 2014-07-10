<?php

namespace Broda\Component\Doctrine\Expr;

use Doctrine\Common\Collections\Expr\ClosureExpressionVisitor;

/**
 * Classe NotStrictClosureExpressionVisitor
 *
 * @author raphael
 */
class NotStrictClosureExpressionVisitor extends ClosureExpressionVisitor
{
    public function walkComparison(Comparison $comparison)
    {
        $field = $comparison->getField();
        $value = $comparison->getValue()->getValue(); // shortcut for walkValue()

        switch ($comparison->getOperator()) {
            case Comparison::EQ:
                return function ($object) use ($field, $value) {
                    return static::getObjectFieldValue($object, $field) == $value;
                };

            case Comparison::NEQ:
                return function ($object) use ($field, $value) {
                    return static::getObjectFieldValue($object, $field) != $value;
                };

            case Comparison::CONTAINS:
                return function ($object) use ($field, $value) {
                    return false !== strpos(strtolower(static::getObjectFieldValue($object, $field)), strtolower($value));
                };

        }
        return parent::walkComparison($comparison);
    }
}
