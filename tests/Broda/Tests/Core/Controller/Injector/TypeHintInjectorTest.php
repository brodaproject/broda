<?php

namespace Broda\Tests\Core\Controller\Injector;

use Broda\Core\Controller\Injector\TypeHintInjector;

class TypeHintInjectorTest extends BaseInjectorTest
{

    /**
     * @var TypeHintInjector
     */
    private $injector;

    protected function setUp()
    {
        parent::setUp();
        require_once __DIR__.'/TypeHint/stubs.php';
        $this->injector = new TypeHintInjector($this->container);
    }

    public function testContainerInjection()
    {
        $instance = $this->injector->createInstance(__NAMESPACE__.'\TypeHint\Injectable1');

        $this->assertSame($this->container, $instance->params[0]);
        $this->assertSame($this->container, $instance->params[1]);
    }

    public function testServiceInjectionByName()
    {
        $instance = $this->injector->createInstance(__NAMESPACE__.'\TypeHint\Injectable2');

        $this->assertSame($this->container['namespace.service_x'], $instance->params[0]);
        $this->assertSame($this->container['namespace.service_x'], $instance->params[1]);
    }

    public function testMultipleServiceInjectionByName()
    {
        $instance = $this->injector->createInstance(__NAMESPACE__.'\TypeHint\Injectable2Multiple');

        $this->assertSame($this->container['namespace.service_x'], $instance->params[0]);
        $this->assertSame($this->container['namespace.service_y'], $instance->params[1]);
        $this->assertSame($this->container['namespace.service_x'], $instance->params[2]);
        $this->assertSame($this->container['namespace.service_y'], $instance->params[3]);
    }

    public function testServiceInjectionByTypeHint()
    {
        $instance = $this->injector->createInstance(__NAMESPACE__.'\TypeHint\Injectable3');

        $this->assertSame($this->container['service_z_y_x'], $instance->params[0]);
        $this->assertSame($this->container['service_z_y_x'], $instance->params[1]);
    }

    public function testServiceInjectionByUnknownService()
    {
        $instance = $this->injector->createInstance(__NAMESPACE__.'\TypeHint\Injectable4');

        $o1 = new TypeHint\UnknownService();
        $o2 = new TypeHint\UnknownServiceWithDependencies();
        $o2->setDep($this->container['namespace.service_y']);

        $this->assertEquals($o1, $instance->params[0]);
        $this->assertEquals($o2, $instance->params[1]);

        // As instancias de classes criadas pelo proprio injector não são as mesmas,
        // são criadas na hora, ou seja, são instancias diferentes
        $this->assertNotSame($o1, $instance->params[0]);
        $this->assertNotSame($o2, $instance->params[1]);
    }

    public function testServiceInjectionByTypeHintInterface()
    {
        $instance = $this->injector->createInstance(__NAMESPACE__.'\TypeHint\Injectable5');

        $this->assertSame($this->container['namespace.service_x'], $instance->params[0]);
        $this->assertSame($this->container['namespace.service_x'], $instance->params[1]);

        // Os dois serviços implementam ArrayAccess, porém como o service_x
        // foi definido primeiro, será ele que será injetado ao invés do service_z_y_x
        $this->assertNotSame($this->container['service_z_y_x'], $instance->params[0]);
    }

}
