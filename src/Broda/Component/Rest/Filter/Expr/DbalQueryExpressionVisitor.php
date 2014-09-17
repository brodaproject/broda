<?php

namespace Broda\Component\Rest\Filter\Expr;

use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use Doctrine\Common\Collections\Expr\Value;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;

/**
 *
 */
class DbalQueryExpressionVisitor extends AbstractQueryExpressionVisitor
{
    /**
     * @var array
     */
    private static $operatorMap = array(
        Comparison::GT => ExpressionBuilder::GT,
        Comparison::GTE => ExpressionBuilder::GTE,
        Comparison::LT  => ExpressionBuilder::LT,
        Comparison::LTE => ExpressionBuilder::LTE,
    );

    /**
     * @var Connection
     */
    private $conn;

    /**
     * @var ExpressionBuilder
     */
    private $expr;

    /**
     * Construtor
     *
     * @param Connection $connection
     * @param array $rootAliases
     * @param array $fieldMap
     */
    public function __construct(Connection $connection, array $rootAliases, array $fieldMap = array())
    {
        $this->rootAliases = $rootAliases;
        $this->fieldMap = $fieldMap;
        $this->conn = $connection;
        $this->expr = $this->conn->getExpressionBuilder();
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
     * {@inheritdoc}
     */
    protected function doWalkCompositeExpression($type, array $expressionList)
    {
        switch($type) {
            case CompositeExpression::TYPE_AND:
                return $this->expr->andX($expressionList);

            case CompositeExpression::TYPE_OR:
                return $this->expr->orX($expressionList);

            default:
                throw new \RuntimeException("Unknown composite " . $type);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doWalkComparison($fieldName, $operator, $placeholder)
    {
        switch ($operator) {
            case Comparison::IN:
                return $this->expr->in($fieldName, $placeholder);

            case Comparison::NIN:
                return $this->expr->notIn($fieldName, $placeholder);

            case Comparison::EQ:
            case Comparison::IS:
                if ($placeholder === null) {
                    return $this->expr->isNull($fieldName);
                }
                return $this->expr->eq($fieldName, $placeholder);

            case Comparison::NEQ:
                if ($placeholder === null) {
                    return $this->expr->isNotNull($fieldName);
                }
                return $this->expr->neq($fieldName, $placeholder);

            case Comparison::CONTAINS:
                return $this->expr->like($fieldName, $placeholder);

            default:
                $operator = self::convertComparisonOperator($operator);
                if ($operator) {
                    return $this->expr->comparison($fieldName, $operator, $placeholder);
                }

                throw new \RuntimeException("Unknown comparison operator: " . $operator);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function doWalkValue($value)
    {
        return $value;
    }

}
