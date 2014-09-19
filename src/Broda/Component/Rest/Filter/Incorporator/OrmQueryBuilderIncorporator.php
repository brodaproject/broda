<?php

namespace Broda\Component\Rest\Filter\Incorporator;

use Broda\Component\Rest\Filter\Expr\OrmQueryExpressionVisitor;
use Broda\Component\Rest\Filter\FilterInterface;

use Doctrine\ORM\QueryBuilder;


class OrmQueryBuilderIncorporator extends SelectableIncorporator implements JoinableIncorporatorInterface
{

    protected $fieldMap = array();

    /**
     * {@inheritdoc}
     */
    public function incorporate($qb, FilterInterface $filter)
    {
        /* @var $qb QueryBuilder */
        $qbFiltered = clone $qb;
        $this->incorporateOrmQueryBuilder($qbFiltered, $filter);

        return $qbFiltered->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_OBJECT);
    }

    /**
     * {@inheritdoc}
     */
    public function count($collection, FilterInterface $filter)
    {
        /* @var $collection QueryBuilder */
        $rootAliases = $collection->getRootAliases();

        $qbFilteredCount = clone $collection;
        $qbFilteredCount->select($qbFilteredCount->expr()->count($rootAliases[0]));
        $this->incorporateOrmQueryBuilder($qbFilteredCount, $filter);

        return $qbFilteredCount->getQuery()->getSingleScalarResult();
    }

    /**
     * {@inheritdoc}
     */
    public static function supports($collection)
    {
        return ($collection instanceof QueryBuilder);
    }

    /**
     * {@inheritdoc}
     */
    public function setFieldMap(array $fieldMap)
    {
        $this->fieldMap = $fieldMap;
    }

    /**
     * @internal
     */
    private function incorporateOrmQueryBuilder(QueryBuilder $qb, FilterInterface $filter)
    {
        $criteria = $this->getFilteringCriteria($filter);

        $rootAliases = $qb->getRootAliases();
        $visitor = new OrmQueryExpressionVisitor($rootAliases, $this->fieldMap);

        if ($whereExpression = $criteria->getWhereExpression()) {
            $qb->andWhere($visitor->dispatch($whereExpression));
            foreach ($visitor->getParameters() as $parameter) {
                $qb->getParameters()->add($parameter);
            }
        }

        if ($criteria->getOrderings()) {
            foreach ($criteria->getOrderings() as $sort => $order) {
                $qb->addOrderBy($visitor->getFieldName($sort), $order);
            }
        }

        // Overwrite limits only if they was set in criteria
        if (($firstResult = $criteria->getFirstResult()) !== null) {
            $qb->setFirstResult($firstResult);
        }
        if (($maxResults = $criteria->getMaxResults()) !== null) {
            $qb->setMaxResults($maxResults);
        }
    }

} 