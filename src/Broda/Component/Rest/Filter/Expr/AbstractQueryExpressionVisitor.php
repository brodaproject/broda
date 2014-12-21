<?php

namespace Broda\Component\Rest\Filter\Expr;


use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use Doctrine\Common\Collections\Expr\ExpressionVisitor;
use Doctrine\Common\Collections\Expr\Value;

/**
 * TODO doc
 */
abstract class AbstractQueryExpressionVisitor extends ExpressionVisitor
{

    /**
     * @var array
     */
    protected $rootAliases;

    /**
     * @var array
     */
    protected $fieldMap = array();

    /**
     * @var array
     */
    protected $parameters = array();

    /**
     * Gets bound parameters.
     * Filled after {@link dispach()}.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * Clears parameters.
     *
     * @return void
     */
    public function clearParameters()
    {
        $this->parameters = array();
    }

    /**
     * Retorna o nome do campo com a tabela correta
     *
     * @param string $field
     * @return string
     */
    public function getFieldName($field)
    {
        if (isset($this->fieldMap[$field])/* && in_array($this->fieldMap[$field], $this->rootAliases)*/) { // BUG
            return $this->fieldMap[$field] . '.' . $field;
        }
        return reset($this->rootAliases) . '.' . $field;
    }

    /**
     * {@inheritDoc}
     */
    public function walkCompositeExpression(CompositeExpression $expr)
    {
        $expressionList = array();

        foreach ($expr->getExpressionList() as $child) {
            $expressionList[] = $this->dispatch($child);
        }

        return $this->doWalkCompositeExpression($expr->getType(), $expressionList);
    }

    /**
     * {@inheritDoc}
     */
    public function walkComparison(Comparison $comparison)
    {
        $parameterName = str_replace('.', '_', $comparison->getField());
        $value = $this->walkValue($comparison->getValue());
        if ($comparison->getOperator() === Comparison::CONTAINS) {
            $value = "%$value%";
        }

        $placeholder = null;

        if ($this->walkValue($comparison->getValue()) !== null) {
            $this->parameters[$parameterName] = $value;
            $placeholder = ':' . $parameterName;
        }

        return $this->doWalkComparison(
            $this->getFieldName($comparison->getField()),
            $comparison->getOperator(),
            $placeholder);
    }

    /**
     * {@inheritDoc}
     */
    public function walkValue(Value $value)
    {
        return $this->doWalkValue($value->getValue());
    }

    abstract protected function doWalkCompositeExpression($type, array $expressionList);

    abstract protected function doWalkComparison($fieldName, $operator, $placeholder);

    abstract protected function doWalkValue($value);

} 