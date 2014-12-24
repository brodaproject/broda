<?php

namespace Broda\Tests\Core\Controller\Injector;

use Broda\Tests\TestCase;
use Pimple\Container;

abstract class BaseInjectorTest extends TestCase
{
    protected $container;

    protected function setUp()
    {
        parent::setUp();

        $c = new Container();

        $c['namespace.service_a'] = new \ArrayObject();
        $c['namespace.service_b'] = function () {
            return new \stdClass();
        };
        // TODO precisa testar factories também?
        $c['namespace.factory_a'] = $c->factory(function () {
            return new \ArrayObject();
        });
        $c['namespace.factory_b'] = $c->factory(function () {
            return new \stdClass();
        });

        $c['service_with_long_name'] = function ($c) {
            $o = new \SplObjectStorage();
            $o->attach($c['namespace.service_a']);
            $o->attach($c['namespace.service_b']);
            return $o;
        };

        $this->container = $c;

    }

}
