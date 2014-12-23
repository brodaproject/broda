<?php

namespace Broda\Tests\Core\Controller\Injector;

use Broda\Core\Controller\Injector\PHPStaticInjector;

class PHPStaticInjectorTest extends BaseInjectorTest
{

    /**
     * @var PHPStaticInjector
     */
    private $injector;

    protected function setUp()
    {
        parent::setUp();
        require_once __DIR__.'/PHPStatic/stubs.php';
        $this->injector = new PHPStaticInjector($this->container);
    }

    public function testContainerInjection()
    {
        $instance = $this->injector->createInstance(__NAMESPACE__.'\PHPStatic\Injectable1');

        $this->assertSame($this->container, $instance->params[0]);
        $this->assertSame($this->container, $instance->params[1]);
    }

    public function testServiceInjectionByName()
    {
        $instance = $this->injector->createInstance(__NAMESPACE__.'\PHPStatic\Injectable2');

        $this->assertSame($this->container['namespace.service_x'], $instance->params[0]);
        $this->assertSame($this->container['namespace.service_x'], $instance->params[1]);
    }

    public function testMultipleServiceInjectionByName()
    {
        $instance = $this->injector->createInstance(__NAMESPACE__.'\PHPStatic\Injectable3');

        $this->assertSame($this->container['namespace.service_x'], $instance->params[0]);
        $this->assertSame($this->container['namespace.service_y'], $instance->params[1]);
        $this->assertSame($this->container['namespace.service_x'], $instance->params[2]);
        $this->assertSame($this->container['namespace.service_y'], $instance->params[3]);
    }

}
