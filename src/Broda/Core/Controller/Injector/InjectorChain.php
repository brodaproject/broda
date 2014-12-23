<?php

namespace Broda\Core\Controller\Injector;

class InjectorChain implements InjectorInterface
{

    /**
     * @var InjectorInterface[]
     */
    private $injectors = array();

    function __construct(array $injectors = array())
    {
        $this->injectors = $injectors;
    }

    public function add(InjectorInterface $injector)
    {
        $this->injectors[] = $injector;
    }

    /**
     * {@inheritdoc}
     */
    public function createInstance($class)
    {
        $exp = null;
        foreach ($this->injectors as $injector) {
            try {
                return $injector->createInstance($class);
            } catch (\Exception $e) {
                // try another one
                $exp = $e;
            }
        }

        if ($exp) {
            throw $exp;
        }

        return null;
    }


} 