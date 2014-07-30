<?php

namespace Broda\Component\Rest\Filter\Expr;

use Doctrine\ORM\Query\Expr as Expr;
use Doctrine\ORM\Query\QueryExpressionVisitor as BaseQueryExpressionVisitor;
use Doctrine\Common\Collections\Expr\Comparison;

use Doctrine\ORM\Query\Parameter;

/**
 * Converts Collection expressions to Query expressions.
 *
 * @author Kirill chEbba Chebunin <iam@chebba.org>
 * @since 2.4
 */
class QueryExpressionVisitor extends BaseQueryExpressionVisitor
{

    /**
     * @var array
     */
    private static $operatorMap = array(
        Comparison::GT => Expr\Comparison::GT,
        Comparison::GTE => Expr\Comparison::GTE,
        Comparison::LT  => Expr\Comparison::LT,
        Comparison::LTE => Expr\Comparison::LTE
    );

    /**
     * @var string[]
     */
    private $rootAliases;

    /**
     *
     * @var array
     */
    private $fieldMap = array();

    /**
     * @var Expr
     */
    private $expr;

    /**
     * Constructor
     *
     * @param string[] $rootAliases
     * @param array $fieldMap
     */
    public function __construct(array $rootAliases, array $fieldMap = array())
    {
        $this->rootAliases = $rootAliases;
        $this->fieldMap = $fieldMap;
        $this->expr = new Expr();
    }

    /**
     * Converts Criteria expression to Query one based on static map.
     *
     * @param string $criteriaOperator
     *
     * @return string|null
     */
    private static function convertComparisonOperator($criteriaOperator)
    {
        return isset(self::$operatorMap[$criteriaOperator]) ? self::$operatorMap[$criteriaOperator] : null;
    }

    /**
     * Retorna o nome do campo com a tabela correta
     *
     * @param type $field
     * @return type
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
    public function walkComparison(Comparison $comparison)
    {
        $parameterName = str_replace('.', '_', $comparison->getField());
        $parameter = new Parameter($parameterName, $this->walkValue($comparison->getValue()));
        $placeholder = ':' . $parameterName;

        switch ($comparison->getOperator()) {
            case Comparison::IN:
                $this->parameters[] = $parameter;
                return $this->expr->in($this->getFieldName($comparison->getField()), $placeholder);

            case Comparison::NIN:
                $this->parameters[] = $parameter;
                return $this->expr->notIn($this->getFieldName($comparison->getField()), $placeholder);

            case Comparison::EQ:
            case Comparison::IS:
                if ($this->walkValue($comparison->getValue()) === null) {
                    return $this->expr->isNull($this->getFieldName($comparison->getField()));
                }
                $this->parameters[] = $parameter;
                return $this->expr->eq($this->getFieldName($comparison->getField()), $placeholder);

            case Comparison::NEQ:
                if ($this->walkValue($comparison->getValue()) === null) {
                    return $this->expr->isNotNull($this->getFieldName($comparison->getField()));
                }
                $this->parameters[] = $parameter;
                return $this->expr->neq($this->getFieldName($comparison->getField()), $placeholder);

            case Comparison::CONTAINS:
                $parameter->setValue('%'.$parameter->getValue().'%', $parameter->getType());
                $this->parameters[] = $parameter;
                return $this->expr->like($this->getFieldName($comparison->getField()), $placeholder);

            default:
                $operator = self::convertComparisonOperator($comparison->getOperator());
                if ($operator) {
                    $this->parameters[] = $parameter;
                    return new Expr\Comparison(
                        $this->getFieldName($comparison->getField()),
                        $operator,
                        $placeholder
                    );
                }

                throw new \RuntimeException("Unknown comparison operator: " . $comparison->getOperator());
        }
    }

}
