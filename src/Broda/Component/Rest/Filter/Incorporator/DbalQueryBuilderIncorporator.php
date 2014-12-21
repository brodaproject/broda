<?php

namespace Broda\Component\Rest\Filter\Incorporator;

use Broda\Component\Rest\Filter\Expr\DbalQueryExpressionVisitor;
use Broda\Component\Rest\Filter\FilterInterface;

use Doctrine\DBAL\Query\QueryBuilder;


class DbalQueryBuilderIncorporator extends SelectableIncorporator implements JoinableIncorporatorInterface
{

    protected $fieldMap = array();

    /**
     * {@inheritdoc}
     */
    public function incorporate($collection, FilterInterface $filter)
    {
        /* @var $collection QueryBuilder */
        if ($collection->getType() !== QueryBuilder::SELECT) {
            throw new \LogicException("Só é permitido DBAL\\QueryBuilder do tipo SELECT");
        }

        $qb = clone $collection;
        $this->incorporateDbalQueryBuilder($qb, $filter);

        /* @var $stmt \Doctrine\DBAL\Driver\Statement */
        $stmt = $qb->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    public function count($collection, FilterInterface $filter)
    {
        /* @var $collection QueryBuilder */
        $qb = clone $collection;
        $qb->select('count(*)');
        $this->incorporateDbalQueryBuilder($qb, $filter);

        return $qb->execute()->fetchColumn(0);
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
    private function incorporateDbalQueryBuilder(QueryBuilder $qb, FilterInterface $filter)
    {
        $criteria = $this->getFilteringCriteria($filter);

        // extraindo os rootAliases, pois o DBAL\QueryBuilder não tem
        $fromPart = $qb->getQueryPart('from');
        $rootAliases = array();
        foreach ($fromPart as $part) {
            $rootAliases[] = $part['alias'];
        }
        $visitor = new DbalQueryExpressionVisitor($qb->getConnection(), $rootAliases, $this->fieldMap);

        if ($whereExpression = $criteria->getWhereExpression()) {
            $qb->andWhere($visitor->dispatch($whereExpression));
            $qb->setParameters($visitor->getParameters());
        }

        if ($criteria->getOrderings()) {
            foreach ($criteria->getOrderings() as $sort => $order) {
                $qb->addOrderBy($visitor->getFieldName($sort), $order);
            }
        }

        if (($firstResult = $criteria->getFirstResult()) !== null) {
            $qb->setFirstResult($firstResult);
        }
        if (($maxResults = $criteria->getMaxResults()) !== null) {
            $qb->setMaxResults($maxResults);
        }
    }

} 