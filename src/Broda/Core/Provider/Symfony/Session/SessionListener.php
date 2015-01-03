<?php

namespace Broda\Core\Provider\Symfony\Session;


use Pimple\Container;
use Symfony\Component\HttpKernel\EventListener\SessionListener as BaseSessionListener;

class SessionListener extends BaseSessionListener
{
    private $container;

    public function __construct(Container $c)
    {
        $this->container = $c;
    }

    protected function getSession()
    {
        if (!isset($this->container['session'])) {
            return null;
        }
        return $this->container['session'];
    }
}