<?php

namespace Broda\Component\Rest\Filter\Incorporator;

use Broda\Component\Rest\Filter\FilterInterface;

/**
 *
 * @author raphael
 */
class IncorporatorFactory
{
    private static $incorporators = array(
        'Broda\Component\Rest\Filter\Incorporator\OrmQueryBuilderIncorporator',
        'Broda\Component\Rest\Filter\Incorporator\DbalQueryBuilderIncorporator',
        'Broda\Component\Rest\Filter\Incorporator\SelectableIncorporator',
    );

    /**
     * @param mixed $class
     * @return IncorporatorInterface
     * @throws \Exception
     */
    public function getIncorporator($class)
    {
        foreach (self::$incorporators as $incorporator) {
            /* @var $incorporator IncorporatorInterface */
            if ($incorporator::supports($class)) {
                return new $incorporator();
            }
        }
        throw new \RuntimeException(sprintf('Incorporator %s não registrado', $class));
    }

    public static function addIncorporator($incorpClass)
    {
        if (!is_subclass_of($incorpClass,
            'Broda\Component\Rest\Filter\Incorporator\IncorporatorInterface')) {
            throw new \UnexpectedValueException(sprintf('Incorporator %s não é valido', $incorpClass));
        }
        if (!in_array($incorpClass, self::$incorporators)) {
            self::$incorporators[] = $incorpClass;
        }
    }

    public function incorporate($object, FilterInterface $filter)
    {
        return $this->getIncorporator($object)->incorporate($object, $filter);
    }

} 