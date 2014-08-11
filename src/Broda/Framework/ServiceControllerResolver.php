<?php

namespace Broda\Framework;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

/**
 * Enables name_of_service:method_name syntax for declaring controllers.
 *
 * @link http://silex.sensiolabs.org/doc/providers/service_controller.html
 */
class ServiceControllerResolver implements ControllerResolverInterface
{

    private $controllerResolver;
    private $containerServiceResolver;

    /**
     * Constructor.
     *
     * @param ControllerResolverInterface $controllerResolver A ControllerResolverInterface instance to delegate to
     * @param CallbackResolver            $callbackResolver   A service resolver instance
     */
    public function __construct(ControllerResolverInterface $controllerResolver,
            ContainerServiceResolver $callbackResolver)
    {
        $this->controllerResolver = $controllerResolver;
        $this->containerServiceResolver = $callbackResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getController(Request $request)
    {
        $controller = $request->attributes->get('_controller', null);

        if ($this->containerServiceResolver->isValid($controller)) {
            return $this->containerServiceResolver->convertCallback($controller);
        }

        return $this->controllerResolver->getController($request);
    }

    /**
     * {@inheritdoc}
     */
    public function getArguments(Request $request, $controller)
    {
        return $this->controllerResolver->getArguments($request, $controller);
    }

}
