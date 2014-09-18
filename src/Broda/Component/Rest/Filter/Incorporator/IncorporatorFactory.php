<?php

namespace Broda\Component\Rest\Filter\Incorporator;


use Broda\Component\Rest\Filter\FilterInterface;
use Broda\Component\Rest\RestService;

class IncorporatorFactory
{
    private static $incorporators = array(
        'Doctrine\ORM\QueryBuilder' =>
            'Broda\Component\Rest\Filter\Incorporator\OrmQueryBuilderIncorporator',
        'Doctrine\DBAL\Query\QueryBuilder' =>
            'Broda\Component\Rest\Filter\Incorporator\DbalQueryBuilderIncorporator',
        'Doctrine\Common\Collections\Selectable' =>
            'Broda\Component\Rest\Filter\Incorporator\SelectableIncorporator',
    );

    /**
     * @var RestService
     */
    private $rest;

    /**
     * Construtor
     *
     * @param RestService $rest
     */
    function __construct(RestService $rest)
    {
        $this->rest = $rest;
    }

    /**
     * @param mixed $class
     * @return IncorporatorInterface
     * @throws \Exception
     */
    public function getIncorporator($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        $incorpClass = $this->getIncorporatorClass($class);
        if (null === $incorpClass) {
            throw new \UnexpectedValueException('Incorporator '.$class.' não registrado');
        }
        $incorporator = new $incorpClass($this->rest);
        if (!$incorporator instanceof IncorporatorInterface) {
            throw new \UnexpectedValueException('Incorporator '.$class.' não é valido');
        }
        return $incorporator;
    }

    private function getIncorporatorClass($objectClass)
    {
        if (isset(self::$incorporators[$objectClass])) {
            return self::$incorporators[$objectClass];
        }
        foreach (self::$incorporators as $keyClass => $valueClass) {
            if (is_subclass_of($objectClass, $keyClass)) {
                return $valueClass;
            }
        }
    }

    public function incorporate($object, FilterInterface $filter)
    {
        return $this->getIncorporator($object)->incorporate($object, $filter);
    }

} 