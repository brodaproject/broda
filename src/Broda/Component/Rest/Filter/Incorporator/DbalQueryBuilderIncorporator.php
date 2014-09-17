<?php

namespace Broda\Component\Rest\Filter\Incorporator;

use Broda\Component\Rest\Filter\Expr\DbalQueryExpressionVisitor;
use Broda\Component\Rest\Filter\FilterInterface;
use Broda\Component\Rest\Filter\TotalizableInterface;
use Broda\Component\Rest\RestService;

use Doctrine\DBAL\Query\QueryBuilder;


class DbalQueryBuilderIncorporator extends SelectableIncorporator
{
    public function incorporate($qb, FilterInterface $filter)
    {
        /* @var $qb QueryBuilder */
        if ($qb->getType() !== QueryBuilder::SELECT) {
            throw new \LogicException("Só é permitido DBAL\\QueryBuilder do tipo SELECT");
        }

        $qbFiltered = clone $qb;
        $this->incorporateDbalQueryBuilder($qbFiltered, $filter);

        if ($filter instanceof TotalizableInterface) {

            switch ($totalizableMode = $this->rest->getTotalizableMode()) {
                case RestService::TOTALIZABLE_ALL:
                case RestService::TOTALIZABLE_ONLY_FILTERED:

                    $qbFilteredCount = clone $qb;
                    $qbFilteredCount->select('count(*)');
                    $this->incorporateDbalQueryBuilder($qbFilteredCount,
                        $filter->createFilterForTotalFilteredRecords());

                    $totalFiltered = $qbFilteredCount->execute()->fetchColumn(0);

                    if ($totalizableMode === RestService::TOTALIZABLE_ALL) {
                        // faz mais um SELECT pra pegar o total de registros sem filtragem (EXPENSIVE!)

                        $qbFilteredCount = clone $qb;
                        $qbFilteredCount->select('count(*)');
                        $this->incorporateDbalQueryBuilder($qbFilteredCount,
                            $filter->createFilterForTotalRecords());

                        $total = $qbFilteredCount->execute()->fetchColumn(0);

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

        /* @var $stmt \Doctrine\DBAL\Driver\Statement */
        $stmt = $qbFiltered->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * @internal
     */
    private function incorporateDbalQueryBuilder(QueryBuilder $qb, FilterInterface $filter)
    {
        $criteria = $this->getFilteringCriteria($filter);

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

        // Overwrite limits only if they was set in criteria
        if (($firstResult = $criteria->getFirstResult()) !== null) {
            $qb->setFirstResult($firstResult);
        }
        if (($maxResults = $criteria->getMaxResults()) !== null) {
            $qb->setMaxResults($maxResults);
        }
    }

} 