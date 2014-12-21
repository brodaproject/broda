<?php

namespace Broda\Component\Rest\Filter\Incorporator;

use Broda\Component\Rest\Filter\FilterInterface;

/**
 * TODO doc
 *
 * @author raphael
 */
class IncorporatorFactory
{

    /**
     * Incorporators registrados
     *
     * @var IncorporatorInterface[]
     */
    private static $incorporators = array(
        'Broda\Component\Rest\Filter\Incorporator\OrmQueryBuilderIncorporator',
        'Broda\Component\Rest\Filter\Incorporator\DbalQueryBuilderIncorporator',
        'Broda\Component\Rest\Filter\Incorporator\SelectableIncorporator',
    );

    /**
     * Retorna o incorporator registrado para a coleção ($object).
     *
     * @param mixed $object
     * @return IncorporatorInterface
     * @throws \RuntimeException Se o incorporator para a coleção não for encontrado
     */
    public function getIncorporator($object)
    {
        foreach (self::$incorporators as $incorporator) {
            if ($incorporator::supports($object)) {
                return new $incorporator();
            }
        }
        throw new \RuntimeException(sprintf('Incorporator para %s não registrado', $object));
    }

    /**
     * Registra um incorporator.
     *
     * @param string $incorpClass FQCN do incorporator a ser registrado
     * @throws \UnexpectedValueException
     */
    public static function addIncorporatorClass($incorpClass)
    {
        if (!is_subclass_of($incorpClass,
            'Broda\Component\Rest\Filter\Incorporator\IncorporatorInterface')) {
            throw new \UnexpectedValueException(sprintf('Incorporator %s não é valido', $incorpClass));
        }
        if (!in_array($incorpClass, self::$incorporators)) {
            self::$incorporators[] = $incorpClass;
        }
    }

    /**
     * Incorpora um filtro à coleção ($object) escolhendo o incorporator
     * ideal para o tipo de coleção.
     *
     * Não é suportado {@link JoinableIncorporatorInterface} neste método.
     *
     * @param mixed           $object Coleção a ser filtrada
     * @param FilterInterface $filter Filtro definido pelo usuário
     * @return mixed Coleção filtrada
     * @throws \RuntimeException Se o incorporator para a coleção não for encontrado
     */
    public function incorporate($object, FilterInterface $filter)
    {
        return $this->getIncorporator($object)->incorporate($object, $filter);
    }

} 