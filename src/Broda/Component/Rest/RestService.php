<?php

namespace Broda\Component\Rest;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Symfony\Component\HttpFoundation\ParameterBag;
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
     * @param \Symfony\Component\HttpFoundation\Request $request
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
     * Get the criteria already prefiltered with params from a request
     *
     * @param \Symfony\Component\HttpFoundation\ParameterBag $request
     * @param \Doctrine\Common\Collections\Criteria $criteria
     * @return \Doctrine\Common\Collections\Criteria
     */
    public function getFilteringCriteria(ParameterBag $request, Criteria $criteria = null)
    {
        if (null === $criteria) $criteria = new Criteria();
        $criteriaExpr = $criteria->expr();

        $columns = $request->get('columns', array());
        $orders = $request->get('order', array());
        $start = (int)$request->get('start');
        $length = min(50, (int)$request->get('length', 30)); // max 50 lines per request

        $_getColName = function ($colIndex) use ($columns) {
            return $columns[$colIndex]['name'] ?: $colIndex;
        };

        // defining search especific columns
        $searchExpr = null;
        foreach ($columns as $col) {
            if ($col['search']['value']) {
                $search = $col['search']['value'];
                $field = $col['name'];

                if (!isset($searchExpr)) {
                    $searchExpr = $criteriaExpr->contains($field, $search);
                } else {
                    $searchExpr = $criteriaExpr->andX($searchExpr, $criteriaExpr->contains($field, $search));
                }
            }
        }

        // defining search all
        $searchAllExpr = null;
        if ($request->get('search[value]', null, true)) {
            $search = $request->get('search[value]', '', true);

            foreach ($columns as $col) {
                $field = $col['name'];

                if (!isset($searchAllExpr)) {
                    $searchAllExpr = $criteriaExpr->contains($field, $search);
                } else {
                    $searchAllExpr = $criteriaExpr->orX($searchAllExpr, $criteriaExpr->contains($field, $search));
                }
            }
        }

        // defining orderings
        $i = count($orders);
        $orderings = array();
        while ($i--) {
            $field = $_getColName($orders[$i]['column']);
            $dir = strtolower($orders[$i]['dir']) == 'desc' ? Criteria::DESC : Criteria::ASC;

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
     * @param ArrayCollection|array $collection
     * @param \Symfony\Component\HttpFoundation\ParameterBag $request
     * @return type
     * @throws \UnexpectedValueException
     */
    public function filter($collection, ParameterBag $request)
    {
        if (is_array($collection)) {
            $collection = new ArrayCollection($collection);
        }

        if (!($collection instanceof Selectable)) {
            throw new \UnexpectedValueException("RestService::filter() only supports arrays or Selectable objects");
        }

        $criteria = $this->getFilteringCriteria($request);

        // do the matching
        $result = $collection->matching($criteria);

        return $result;
    }

}
