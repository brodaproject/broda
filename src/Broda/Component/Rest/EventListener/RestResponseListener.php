<?php

namespace Broda\Component\Rest\EventListener;

use Broda\Component\Rest\RestResponse;
use Broda\Component\Rest\RestService;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 *
 */
class RestResponseListener implements EventSubscriberInterface
{

    /**
     *
     * @var RestService
     */
    protected $rest;

    /**
     *
     * @var LoaderInterface
     */
    protected $loader;

    public function __construct(RestService $rest, LoaderInterface $loader = null)
    {
        $this->rest = $rest;
        $this->loader = $loader;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        if (null !== $this->loader) {
            // carrega os annotations para criar as rotas dos rests
            $this->loader->load('');
        }
    }

    /**
     * Handles string responses.
     *
     * @param GetResponseForControllerResultEvent $event The event to handle
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $response = $event->getControllerResult();
        $request = $event->getRequest();

        if ($response instanceof RestResponse) {

            $format = $request->getRequestFormat(null);
            if (null === $format) {
                $accepts = $request->getAcceptableContentTypes();
                $format = $request->getFormat($accepts[0]);
            }

            $newResponse = new Response($this->rest->formatOutput($response->getData(), $format),
                200,
                array(
                    "Content-Type" => $request->getMimeType($format)
                )
            );

            $event->setResponse($newResponse);

        }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::REQUEST => array(array('onKernelRequest', 64)), // symfony is 32
            KernelEvents::VIEW => array('onKernelView', 60), // silex is -10
        );
    }
}
