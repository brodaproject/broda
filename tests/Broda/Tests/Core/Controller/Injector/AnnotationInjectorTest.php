<?php

namespace Broda\Tests\Core\Controller\Injector;

use Broda\Core\Controller\Injector\AnnotationInjector;
use Doctrine\Common\Annotations\AnnotationReader;

class AnnotationInjectorTest extends BaseInjectorTest
{

    /**
     * @var AnnotationInjector
     */
    private $injector;

    protected function setUp()
    {
        parent::setUp();
        require_once __DIR__.'/Annotation/stubs.php';
        $this->injector = new AnnotationInjector($this->container, new AnnotationReader());
    }

    public function testContainerInjection()
    {
        $instance = $this->injector->createInstance(__NAMESPACE__.'\Annotation\Injectable1');

        $this->assertSame($this->container, $instance->params[0]);
        $this->assertSame($this->container, $instance->params[1]);
    }

    public function testServiceInjectionByName()
    {
        $instance = $this->injector->createInstance(__NAMESPACE__.'\Annotation\Injectable2');

        $this->assertSame($this->container['namespace.service_x'], $instance->params[0]);
        $this->assertSame($this->container['namespace.service_x'], $instance->params[1]);
    }

    public function testMultipleServiceInjectionByName()
    {
        $instance = $this->injector->createInstance(__NAMESPACE__.'\Annotation\Injectable3');

        $this->assertSame($this->container['namespace.service_x'], $instance->params[0]);
        $this->assertSame($this->container['namespace.service_y'], $instance->params[1]);
        $this->assertSame($this->container['namespace.service_x'], $instance->params[2]);
        $this->assertSame($this->container['namespace.service_y'], $instance->params[3]);
    }

}
