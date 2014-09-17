<?php

namespace Broda\Component\Rest\Filter\Expr;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\CompositeExpression;
use Doctrine\Common\Collections\Expr\Value;

use Doctrine\ORM\Query\Expr as Expr;
use Doctrine\ORM\Query\Parameter;

/**
 *
 */
class OrmQueryExpressionVisitor extends AbstractQueryExpressionVisitor
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
     * {@inheritdoc}
     *
     * Para o ORM, é retornado um ArrayCollection com objetos do tipo Parameter.
     *
     * @return array|ArrayCollection
     */
    public function getParameters()
    {
        $rawParameters = parent::getParameters();
        $parameters = new ArrayCollection();
        foreach ($rawParameters as $key => $value) {
            $parameters->add(new Parameter($key, $value));
        }
        return $parameters;
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
                return new Expr\Andx($expressionList);

            case CompositeExpression::TYPE_OR:
                return new Expr\Orx($expressionList);

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
                    return new Expr\Comparison(
                        $fieldName,
                        $operator,
                        $placeholder
                    );
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
