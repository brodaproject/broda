<?php

namespace Broda\Component\Rest;

use Broda\Component\Rest\Filter\Expr as FilterExpr;
use Broda\Component\Rest\Filter\Param as FilterParam;
use Broda\Component\Rest\Filter\FilterInterface;
use Broda\Component\Rest\Filter\TotalizableInterface;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\QueryBuilder;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Classe RestService
 *
 * @author Raphael Hardt <raphael.hardt@gmail.com>
 */
class RestService
{
    /**
     *
     * @var Serializer
     */
    protected $serializer;

    /**
     * Constructor.
     *
     * @param Serializer $serializer
     */
    public function __construct(Serializer $serializer = null)
    {
        $this->serializer = $serializer ?: SerializerBuilder::create()->build();
    }

    /**
     * Serializes the object to a required format.
     *
     * Internal. Return a RestResponse instead.
     *
     * @param type $data
     * @param type $format
     * @return type
     */
    public function formatOutput($data, $format)
    {
        return $this->serializer->serialize($data, $format);
    }

    /**
     * Creates a object from request data
     *
     * @param Request $request
     * @param type $class
     * @return type
     */
    public function createObjectFromRequest(Request $request, $class)
    {
        // we must get the raw content, since the deserialization need it raw
        $data = $request->getContent();
        return $this->createObject($data, $class, $request->getContentType());
    }

    /**
     * Creates a object from array data
     *
     * @param type $data
     * @param type $class
     * @param type $format
     * @return type
     */
    public function createObject($data, $class, $format = 'json')
    {
        return $this->serializer->deserialize($data, $class, $format);
    }

    /**
     * Incorpora um filtro à um QueryBuilder do usuário.
     *
     * Todos os pós-filtros serão adicionados ao QueryBuilder. Depois, basta
     * usar o {@link filter} para retornar os dados.
     *
     * @param QueryBuilder $qb
     * @param FilterInterface $filter
     * @param array $fieldMap
     * @return QueryBuilder
     */
    public function incorporateQueryBuilder(QueryBuilder $qb, FilterInterface $filter, array $fieldMap = array())
    {
        $criteria = $this->getFilteringCriteria($filter);

        $rootAliases = $qb->getRootAliases();
        $visitor = new FilterExpr\QueryExpressionVisitor($rootAliases, $fieldMap);

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

        return $qb;
    }

    /**
     * Retorna o/um Criteria com os pósfiltros do FilterInterface.
     *
     * Útil para
     *
     * @param FilterInterface $filter
     * @return Criteria
     */
    public function getFilteringCriteria(FilterInterface $filter)
    {
        $criteria = Criteria::create();
        $expr = Criteria::expr();

        $columns = $filter->getColumns();
        $columnSearchs = $filter->getColumnSearchs();
        $globalSearch = $filter->getGlobalSearch();
        $orders = $filter->getOrderings();
        $start = $filter->getFirstResult();
        $length = $filter->getMaxResults(); // max 50 lines per request

        // defining search especific columns
        $searchExpr = null;
        foreach ($columnSearchs as $col) {
            /* @var $col FilterParam\Searching */
            $field = $col->getColumnName();

            $searchColExpr = null;
            foreach ($col->getTokens() as $search) {
                if (!isset($searchColExpr)) {
                    $searchColExpr = $expr->contains($field, $search);
                } else {
                    $searchColExpr = $expr->andX($searchColExpr, $expr->contains($field, $search));
                }
            }

            if (!isset($searchExpr)) {
                $searchExpr = $searchColExpr;
            } else {
                $searchExpr = $expr->andX($searchExpr, $searchColExpr);
            }
        }

        // defining search all
        $searchAllExpr = null;
        if (null !== $globalSearch) {

            foreach ($columns as $col) {
                /* @var $col FilterParam\Column */
                $field = $col->getName();

                if (!$col->getSearchable()) continue;

                $searchColExpr = null;
                foreach ($globalSearch->getTokens() as $search) {
                    if (!isset($searchColExpr)) {
                        $searchColExpr = $expr->contains($field, $search);
                    } else {
                        $searchColExpr = $expr->andX($searchColExpr, $expr->contains($field, $search));
                    }
                }

                if (!isset($searchAllExpr)) {
                    $searchAllExpr = $searchColExpr;
                } else {
                    $searchAllExpr = $expr->orX($searchAllExpr, $searchColExpr);
                }
            }
        }

        // defining orderings
        $orderings = array();
        foreach ($orders as $order) {
            /* @var $order FilterParam\Ordering */
            $field = $order->getColumn()->getName();
            $dir = $order->getDir();

            if (!$order->getColumn()->getOrderable()) continue;

            $orderings[$field] = $dir;
        }

        // mount criteria
        if (count($searchAllExpr)) $criteria->andWhere($searchAllExpr);
        if (count($searchExpr)) $criteria->andWhere($searchExpr);
        if (count($orderings)) $criteria->orderBy($orderings);
        $criteria->setFirstResult($start);
        $criteria->setMaxResults($length);

        return $criteria;
    }

    /**
     * Filters a collection and return data filtered by request
     *
     * @param Selectable|QueryBuilder|array $collection
     * @param FilterInterface $filter
     * @return type
     * @throws \UnexpectedValueException
     */
    public function filter($collection, FilterInterface $filter)
    {
        if (is_array($collection)) {
            $collection = new ArrayCollection($collection);
        }

        switch (true) {
            case ($collection instanceof Selectable):
                return $this->filterSelectable($collection, $filter);
            case ($collection instanceof QueryBuilder):
                return $this->filterQueryBuilder($collection, $filter);
            default:
                throw new \UnexpectedValueException("RestService::filter() only supports arrays, Selectable or QueryBuilder objects");
        }
    }

    /**
     * @internal
     */
    protected function filterSelectable(Selectable $collection, FilterInterface $filter)
    {
        $criteria = $this->getFilteringCriteria($filter);

        if ($filter instanceof TotalizableInterface) {

            $totalCriteria = Criteria::create();
            $totalCriteria->where($criteria->getWhereExpression());

            $totalCollection = $collection->matching($totalCriteria);
            $filter->setTotalRecords($totalCollection->count());

        }

        return $filter->getOutputResponse($collection->matching($criteria));
    }

    /**
     * @internal
     */
    protected function filterQueryBuilder(QueryBuilder $qb, FilterInterface $filter)
    {
        if ($filter instanceof TotalizableInterface) {
            $rootAliases = $qb->getRootAliases();

            $qbCount = clone $qb;
            $qbCount->select($qbCount->expr()->count($rootAliases[0]));
            $qbCount->setFirstResult(null)->setMaxResults(null);

            $filter->setTotalRecords($qbCount->getQuery()->getSingleScalarResult());
            unset($qbCount);
        }

        $query = $qb->getQuery();

        return $filter->getOutputResponse($query->getResult());
    }

}
