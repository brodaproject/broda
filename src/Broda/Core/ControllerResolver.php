<?php

namespace Broda\Core;

use Broda\Core\Controller\Annotations as Ctrl;
use Broda\Core\Controller\Injector\InjectorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Controller\ControllerResolver as BaseControllerResolver;

/**
 * Classe que substitui a ControllerResolver do Symfony
 * para fazer funcionar as anotações (@)Inject nos
 * controllers.
 *
 * Se a rota for um controller no formato de string (ex: "Classe::metodo")
 * então ele vai criar a classe passando os serviços injetados
 * pelas tags (@)Inject.
 *
 * Mais detalhes ver documentação na tag {@see \CMS\Core\Controller\Annotations\Inject}.
 */
class ControllerResolver extends BaseControllerResolver
{

    /**
     * @var InjectorInterface
     */
    private $injector;

    public function __construct(InjectorInterface $injector, LoggerInterface $logger = null)
    {
        parent::__construct($logger);
        $this->injector = $injector;
    }

    protected function createController($controller)
    {
        if (false === strpos($controller, '::')) {
            throw new \InvalidArgumentException(sprintf('Unable to find controller "%s".', $controller));
        }

        list($class, $method) = explode('::', $controller, 2);

        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        $controller = $this->injector->createInstance($class);

        return array($controller, $method);
    }

} 