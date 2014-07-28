<?php

namespace Broda\Component\Rest;

use Broda\Component\Rest\Filter\FilterInterface;
use Broda\Component\Rest\Filter\Param\Column;
use Broda\Component\Rest\Filter\Param\Ordering;
use Broda\Component\Rest\Filter\Param\Searching;
use Broda\Component\Rest\Filter\TotalizableInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
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
     * Retorna o/um Criteria com os pósfiltros do FilterInterface.
     *
     * Útil para
     *
     * @param FilterInterface $filter
     * @param Criteria $criteria
     * @return Criteria
     */
    public function getFilteringCriteria(FilterInterface $filter, Criteria $criteria = null)
    {
        if (null === $criteria) $criteria = Criteria::create();
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
            /* @var $col Searching */
            $field = $col->getColumnName();

            $searchColExpr = null;
            foreach ($col->getTokens() as $search) {
                
                if (!isset($searchColExpr)) {
                    $searchColExpr = $expr->contains($field, $search);
                } else {
                    $searchColExpr = $expr->orX($searchColExpr, $expr->contains($field, $search));
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

            foreach ($globalSearch->getTokens() as $search) {

                foreach ($columns as $col) {
                    /* @var $col Column */
                    $field = $col->getName();

                    if (!$col->getSearchable()) continue;

                    if (!isset($searchAllExpr)) {
                        $searchAllExpr = $expr->contains($field, $search);
                    } else {
                        $searchAllExpr = $expr->orX($searchAllExpr, $expr->contains($field, $search));
                    }
                }
            }
        }

        // defining orderings
        $orderings = array();
        foreach ($orders as $order) {
            /* @var $order Ordering */
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
     * @param Selectable|array $collection
     * @param FilterInterface $filter
     * @return type
     * @throws \UnexpectedValueException
     */
    public function filter($collection, FilterInterface $filter)
    {
        if (is_array($collection)) {
            $collection = new ArrayCollection($collection);
        }

        if (!($collection instanceof Selectable)) {
            throw new \UnexpectedValueException("RestService::filter() only supports arrays or Selectable objects");
        }

        $criteria = $this->getFilteringCriteria($filter);

        if ($filter instanceof TotalizableInterface) {

            $totalCriteria = Criteria::create();
            $totalCriteria->where($criteria->getWhereExpression());

            $totalCollection = $collection->matching($totalCriteria);
            $filter->setTotalRecords($totalCollection->count());

        }

        // do the matching
        $result = $collection->matching($criteria);

        // select the output method
        return $filter->getOutputResponse($result);
    }

}
