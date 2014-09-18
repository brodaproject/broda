<?php

namespace Broda\Tests\Component\Rest\Mocks;

use Broda\Component\Rest\Filter\AbstractFilter;
use Broda\Component\Rest\Filter\FilterInterface;

/**
 * Classe AbstractFilterMock
 *
 * @author raphael
 */
class AbstractFilterMock extends AbstractFilter
{

    public function __construct()
    {
        $this->columns = self::$defaultColumns;
    }

    public static function createFromFilter($filterClass, FilterInterface $filter)
    {
        $refl = new \ReflectionClass($filterClass);
        if (!$refl->implementsInterface('Broda\Component\Rest\Filter\FilterInterface')) {
            throw new \LogicException('Precisa ser um FilterInterface');
        }

        // instanciate without call constructor
        if (method_exists($refl, 'newInstanceWithoutConstructor')) {
            $newFilter = $refl->newInstanceWithoutConstructor();
        } else {
            $serializedString = sprintf(
                'O:%d:"%s":0:{}',
                strlen($filterClass),
                $filterClass
            );

            $newFilter = unserialize($serializedString);
        }

        /* @var $newFilter FilterInterface */
        foreach ($refl->getProperties() as $property) {
            switch ($name = $property->getName()) {
                case 'columns':
                case 'orderings':
                case 'globalSearch':
                case 'columnSearchs':
                case 'firstResult':
                case 'maxResults':
                    $value = $filter->{'get'.$name}();
                    $property->setAccessible(true);
                    $property->setValue($newFilter, $value);
                    break;
            }
        }

        return $newFilter;
    }

}
