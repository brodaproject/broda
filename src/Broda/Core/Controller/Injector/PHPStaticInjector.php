<?php

namespace Broda\Core\Controller\Injector;

use Pimple\Container;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;

class PHPStaticInjector implements InjectorInterface
{

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
     * @param PropertyAccessorInterface $propAccessor
     */
    function __construct(Container $container, PropertyAccessorInterface $propAccessor = null)
    {
        $this->container = $container;
        $this->propAccessor = $propAccessor ?: PropertyAccess::createPropertyAccessor();
    }

    /**
     * {@inheritdoc}
     */
    public function createInstance($class)
    {
        $reflClass             = new \ReflectionClass($class);
        $reflMethodConstructor = $reflClass->getConstructor();
        $reflMethods           = $reflClass->getMethods(\ReflectionMethod::IS_PUBLIC);

        // Constructor (hard dependency injection)
        try {
            $injectables = $this->getInjectables($reflMethodConstructor, $reflClass->name);
            $constructorParams  = array();
            foreach ($injectables as $injectable) {
                $constructorParams[] = $this->getService($injectable);
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
                    $injectablesMethod = $this->getInjectables($reflMethod, $reflMethod->isStatic() ? $reflClass->name : $instance);

                    $args = array();
                    foreach ($injectablesMethod as $injectable) {
                        $args[] = $this->getService($injectable);
                    }

                    $reflMethod->invokeArgs($instance, $args);
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
     * Retorna os injetáveis do método de acordo com o método correspondente.
     *
     * A convenção é que, caso seja o injetor do construtor, o método deve
     * ser estático e o nome deve ser 'injectConstrutor'.
     * Caso o injetor seja de um setter, basta ter um método (estático ou não,
     * dependendo do setter) com o mesmo nome, trocando o 'set' por 'inject'.
     * Ex: 'setReader' --> 'injectReader'
     *
     * O método deve retornar um array de strings ou um array de arrays
     * com algumas propriedades.
     *
     * TODO: fazer um objeto metadata em vez de retornar array de arrays
     *
     * @param \ReflectionMethod $method
     * @param mixed $context
     * @return array
     */
    private function getInjectables(\ReflectionMethod $method, $context)
    {
        $caller = $this->getCallerMethodName($method->name);

        if (!method_exists($context, $caller)) {
            // Se o método injetor não existe, ignorar, e deixar
            // que o erro do próprio PHP (por falta de parametro) seja
            // ativado sozinho
            return array();
        }

        $rawInjectables = call_user_func(array($context, $caller));

        if (!is_array($rawInjectables)) {
            throw new \InvalidArgumentException("The method '$caller' must return an array'");
        }

        $injectables = array();
        foreach ($rawInjectables as $rawInjectable) {
            if (is_string($rawInjectable)) {
                $injectables[] = array('value' => $rawInjectable);
            } else {
                $injectables[] = $rawInjectable;
            }
        }

        return $injectables;
    }

    /**
     * Retorna o nome do método a ser chamado pelo injetor quando
     * for procurar pelas dependências do objeto.
     *
     * @param string $methodName
     * @return mixed|string
     */
    protected function getCallerMethodName($methodName)
    {
        if ($methodName === '__construct') {
            return 'injectConstructor';
        }
        return str_replace('set', 'inject', $methodName);
    }

    /**
     * Retorna o serviço do container de acordo com a anotação (@)Inject.
     *
     * @param array $injectable
     * @return mixed
     */
    private function getService(array $injectable)
    {
        if ($injectable['value'] === 'CONTAINER') {
            return $this->container;
        }

        $service = $this->container[$injectable['value']];

        if (isset($injectable['key'])) {
            $service = $service[$injectable['key']];
        } elseif (isset($injectable['property'])) {
            $service = $this->propAccessor->getValue($service, $injectable['property']);
        } elseif (isset($injectable['method'])) {
            $service = call_user_func(array($service, $injectable['method']));
        }

        return $service;
    }

} 