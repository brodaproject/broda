<?php

namespace Broda\Core\Controller\Injector;

use Pimple\Container;

class TypeHintInjector implements InjectorInterface
{

    /**
     * @var Container
     */
    private $container;

    /**
     * Construtor.
     *
     * @param Container $container
     */
    function __construct(Container $container)
    {
        $this->container = $container;
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
        $constructorParams  = array();
        if ($reflMethodConstructor) { // se tiver construtor
            foreach ($reflMethodConstructor->getParameters() as $param) {
                if ($injectClass = $param->getClass()) {
                    if (null === $inject = $this->findService($param)) {
                        $inject = $this->createInstance($injectClass->name);
                    }

                    $constructorParams[] = $inject;
                }
            }
        }

        $instance = $reflClass->newInstanceArgs($constructorParams);

        // Setters (soft dependency injection)
        foreach ($reflMethods as $reflMethod) {
            if (0 === strpos($reflMethod->name, 'set')) {
                $methodParams = array();
                foreach ($reflMethod->getParameters() as $param) {
                    if ($injectClass = $param->getClass()) {
                        if (null === $inject = $this->findService($param)) {
                            $inject = $this->createInstance($injectClass->name);
                        }

                        $methodParams[] = $inject;
                    }
                }

                $reflMethod->invokeArgs($instance, $methodParams);
            }
        }

        return $instance;
    }

    /**
     * Procura pelo serviço no container de acordo com o parametro.
     *
     * Primeiro ele verifica se o nome do parametro já não é o nome de
     * algum serviço definido. Caso não seja encontrado, ele procura
     * em todos os serviços disponíveis do container e retorna o
     * serviço que for instância da classe type-hinteada.
     *
     * @param \ReflectionParameter $refl
     * @return mixed|null
     */
    private function findService(\ReflectionParameter $refl)
    {
        if ($refl->getClass()->name === 'Pimple\Container'
            || $refl->getClass()->isSubclassOf('Pimple\Container')
        ) {
            return $this->container;
        }

        // Tenta procurar pelo nome do parametro
        $serviceName = $this->normalizeServiceName($refl->name);
        if (isset($this->container[$serviceName])) {
            $service = $this->container[$serviceName];
            if ($refl->getClass()->isInstance($service)) {
                return $service;
            }
        }

        // Tenta procurar em todos os serviços do container se
        // não tem algum que é instância daquela classe
        foreach ($this->container->keys() as $key) {
            $service = $this->container[$key];
            if ($refl->getClass()->isInstance($service)) {
                return $service;
            }
        }

        return null;
    }

    /**
     * Transforma um nome 'namespace_nomeDoServico' em 'namespace.nome_do_servico'
     *
     * @param string $camelCasedName
     * @return string
     */
    private function normalizeServiceName($camelCasedName)
    {
        $camelCasedName = str_replace('_', '.', $camelCasedName);
        return strtolower(preg_replace('~(?<=\\w)([A-Z])~', '_$1', $camelCasedName));
    }

} 