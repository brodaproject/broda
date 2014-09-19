<?php

namespace Broda\Framework\Controller;

use Broda\Framework\ServiceResolver;
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
    private $serviceResolver;

    /**
     * Constructor.
     *
     * @param ControllerResolverInterface $controllerResolver A ControllerResolverInterface instance to delegate to
     * @param ServiceResolver             $callbackResolver   A service resolver instance
     */
    public function __construct(ControllerResolverInterface $controllerResolver, ServiceResolver $callbackResolver)
    {
        $this->controllerResolver = $controllerResolver;
        $this->serviceResolver = $callbackResolver;
    }

    /**
     * {@inheritdoc}
     */
    public function getController(Request $request)
    {
        $controller = $request->attributes->get('_controller', null);

        if ($this->serviceResolver->isValid($controller)) {
            return $this->serviceResolver->convertCallback($controller);
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
