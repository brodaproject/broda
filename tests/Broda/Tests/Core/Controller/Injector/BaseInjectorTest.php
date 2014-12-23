<?php

namespace Broda\Tests\Core\Controller\Injector;


use Pimple\Container;

abstract class BaseInjectorTest extends \PHPUnit_Framework_TestCase
{
    protected $container;

    protected function setUp()
    {
        $this->container = new Container();
        $this->container['namespace.service_x'] = new \ArrayObject();
        $this->container['namespace.service_y'] = new \stdClass();
        $this->container['service_z_y_x'] = function ($c) {
            $o = new \SplObjectStorage();
            $o->attach($c['namespace.service_x']);
            return $o;
        };

    }

}
