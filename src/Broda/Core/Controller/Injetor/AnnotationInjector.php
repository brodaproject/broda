<?php

namespace Broda\Core\Controller\Injector;

use Broda\Core\Controller\Annotations;
use Doctrine\Common\Annotations\Reader;
use Pimple\Container;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

/**
 * Classe que faz funcionar as anotações (@)Inject nos
 * controllers.
 *
 * É usado pelo {@link Broda\Core\ControllerResolver} para injetar
 * dependências automaticamente nos controllers.
 */
class AnnotationInjector implements InjectorInterface
{

    /**
     * Constante para dizer ao (@)Inject que deve ser injetado o próprio container
     * na classe.
     *
     * Use dessa forma: (@)Inject(Broda\Core\Controller\Injector\Injector::CONTAINER)
     */
    const CONTAINER = 'CONTAINER';

    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var PropertyAccessorInterface
     */
    private $propAccessor;

    /**
     * @var Container
     */
    private $container;

    /**
     * Construtor.
     *
     * @param Container $container
     * @param Reader $reader
     * @param PropertyAccessorInterface $propAccessor
     */
    function __construct(Container $container, Reader $reader, PropertyAccessorInterface $propAccessor = null)
    {
        $this->container = $container;
        $this->propAccessor = $propAccessor ?: PropertyAccess::createPropertyAccessor();
        $this->reader = $reader;
    }


    /**
     * Cria uma instância da classe com os serviços injetados.
     *
     * Este método vai ler o __construct e os métodos setters da
     * classe para saber quais serviços deve injetar nela.
     *
     * @param string $class Nome da classe a ser instanciada
     * @return mixed Instância da classe
     */
    public function createInstance($class)
    {
        $reflClass             = new \ReflectionClass($class);
        $reflMethodConstructor = $reflClass->getConstructor();
        $reflMethods           = $reflClass->getMethods(\ReflectionMethod::IS_PUBLIC);

        // Constructor (hard dependency injection)
        try {
            $injectAnnotsConstr = $this->reader->getMethodAnnotations($reflMethodConstructor);
            $constructorParams  = array();
            foreach ($injectAnnotsConstr as $injectAnnot) {
                if ($injectAnnot instanceof Annotations\Inject) {
                    $constructorParams[] = $this->getService($injectAnnot);
                }
            }

        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException(
                $e->getMessage() . ' (no método "'.$reflClass->name.'::__construct")',
                $e->getCode(), $e);
        }

        $instance = $reflClass->newInstanceArgs($constructorParams);

        // Setters (soft dependency injection)
        foreach ($reflMethods as $reflMethod) {
            try {
                if (0 === strpos($reflMethod->name, 'set')) {
                    $injectAnnotsMethods = $this->reader->getMethodAnnotations($reflMethod);

                    foreach ($injectAnnotsMethods as $injectAnnot) {
                        if ($injectAnnot instanceof Annotations\Inject) {
                            $reflMethod->invoke($instance, $this->getService($injectAnnot));
                            break;
                        }
                    }
                } else {
                    // Verifica se não foi colocada alguma (@)Inject
                    // em algum outro método senão um setter
                    if ($this->reader->getMethodAnnotation($reflMethod, 'CMS\Core\Controller\Annotations\Inject')) {
                        throw new \LogicException('As anotações @Inject só podem ser '.
                            'usadas em construtores ou em métodos "set" (onde há injeção '.
                            'de dependências).');
                    }
                }
            } catch (\InvalidArgumentException $e) {
                throw new \InvalidArgumentException(
                    $e->getMessage() . ' (no método "' . $reflClass->name . '::' . $reflMethod->name . '")',
                    $e->getCode(), $e);
            }
        }

        return $instance;
    }

    /**
     * Retorna o serviço do container de acordo com a anotação (@)Inject.
     *
     * @param Annotations\Inject $injectAnnot
     * @return mixed
     */
    private function getService(Annotations\Inject $injectAnnot)
    {
        if ($injectAnnot->value === self::CONTAINER) {
            return $this->container;
        }

        $service = $this->container[$injectAnnot->value];

        if ($injectAnnot->key) {
            $service = $service[$injectAnnot->key];
        } elseif ($injectAnnot->property) {
            $service = $this->propAccessor->getValue($service, $injectAnnot->property);
        } elseif ($injectAnnot->method) {
            $service = call_user_func(array($service, $injectAnnot->method));
        }

        return $service;
    }
} 