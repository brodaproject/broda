<?php

namespace Broda\Component\Rest\Filter\Incorporator;

use Broda\Component\Rest\Filter\Expr\OrmQueryExpressionVisitor;
use Broda\Component\Rest\Filter\FilterInterface;
use Broda\Component\Rest\Filter\TotalizableInterface;
use Broda\Component\Rest\RestService;

use Doctrine\ORM\QueryBuilder;


class OrmQueryBuilderIncorporator extends SelectableIncorporator
{
    public function incorporate($qb, FilterInterface $filter)
    {
        /* @var $qb QueryBuilder */
        $qbFiltered = clone $qb;
        $this->incorporateOrmQueryBuilder($qbFiltered, $filter);

        if ($filter instanceof TotalizableInterface) {

            switch ($totalizableMode = $this->rest->getTotalizableMode()) {
                case RestService::TOTALIZABLE_ALL:
                case RestService::TOTALIZABLE_ONLY_FILTERED:

                    $rootAliases = $qb->getRootAliases();

                    $qbFilteredCount = clone $qb;
                    $qbFilteredCount->select($qbFilteredCount->expr()->count($rootAliases[0]));
                    $this->incorporateOrmQueryBuilder($qbFilteredCount,
                        $filter->createFilterForTotalFilteredRecords());

                    $totalFiltered = $qbFilteredCount->getQuery()->getSingleScalarResult();

                    if ($totalizableMode === RestService::TOTALIZABLE_ALL) {
                        // faz mais um SELECT pra pegar o total de registros sem filtragem (EXPENSIVE!)

                        $qbFilteredCount = clone $qb;
                        $qbFilteredCount->select($qbFilteredCount->expr()->count($rootAliases[0]));
                        $this->incorporateOrmQueryBuilder($qbFilteredCount,
                            $filter->createFilterForTotalRecords());

                        $total = $qbFilteredCount->getQuery()->getSingleScalarResult();

                    } else {
                        // (FAST)
                        $total = $totalFiltered;
                    }

                    $filter->setTotalRecords($total, $totalFiltered);
                    unset($qbFilteredCount);
                    break;
                case RestService::TOTALIZABLE_UNKNOWN:
                    $filter->setTotalRecords(
                        $filter->getFirstResult() + $filter->getMaxResults() + 1
                    );
                    break;
            }

        }

        $query = $qbFiltered->getQuery();
        return $query->getResult(\Doctrine\ORM\Query::HYDRATE_OBJECT);
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