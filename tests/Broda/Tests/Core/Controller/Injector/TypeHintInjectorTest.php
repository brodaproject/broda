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
        $this->injector = new TypeHintInjector($this->container);
    }

    public function testContainerInjection()
    {
        eval(<<<PHP
class TypeHintInjectAHard {
    public \$inject;
    function __construct(Pimple\Container \$c) {
        \$this->inject = \$c;
    }
}
class TypeHintInjectASoft {
    public \$inject;
    function setContainer(Pimple\Container \$c) {
        \$this->inject = \$c;
    }
}
PHP
        );

        $instance = $this->injector->createInstance('TypeHintInjectAHard');
        $this->assertSame($this->container, $instance->inject);

        $instance = $this->injector->createInstance('TypeHintInjectASoft');
        $this->assertSame($this->container, $instance->inject);
    }

    public function testServiceInjectionByName()
    {
        eval(<<<PHP
class TypeHintInjectBHard {
    public \$inject;
    function __construct(ArrayObject \$namespace_serviceA) {
        \$this->inject = \$namespace_serviceA;
    }
}
class TypeHintInjectBSoft {
    public \$inject;
    function setContainer(ArrayObject \$namespace_serviceA) {
        \$this->inject = \$namespace_serviceA;
    }
}
PHP
        );

        $instance = $this->injector->createInstance('TypeHintInjectBHard');
        $this->assertSame($this->container['namespace.service_a'], $instance->inject);

        $instance = $this->injector->createInstance('TypeHintInjectBSoft');
        $this->assertSame($this->container['namespace.service_a'], $instance->inject);
    }

    public function testMultipleServiceInjectionByName()
    {
        eval(<<<PHP
class TypeHintInjectC {
    public \$injectHard1, \$injectHard2;
    public \$injectSoft1, \$injectSoft2;
    function __construct(ArrayObject \$namespace_serviceA, stdClass \$namespace_serviceB) {
        \$this->injectHard1 = \$namespace_serviceA;
        \$this->injectHard2 = \$namespace_serviceB;
    }
    function setServices(ArrayObject \$namespace_serviceA, stdClass \$namespace_serviceB) {
        \$this->injectSoft1 = \$namespace_serviceA;
        \$this->injectSoft2 = \$namespace_serviceB;
    }
}
PHP
        );

        $instance = $this->injector->createInstance('TypeHintInjectC');

        $this->assertSame($this->container['namespace.service_a'], $instance->injectHard1);
        $this->assertSame($this->container['namespace.service_b'], $instance->injectHard2);
        $this->assertSame($this->container['namespace.service_a'], $instance->injectSoft1);
        $this->assertSame($this->container['namespace.service_b'], $instance->injectSoft2);
    }

    public function testServiceInjectionByNameWithTypeHintWrong()
    {
        eval(<<<PHP
class TypeHintInjectCWrong {
    public \$inject;
    function __construct(stdClass \$namespace_serviceA) {
        \$this->inject = \$namespace_serviceA;
    }
}
PHP
        );

        $instance = $this->injector->createInstance('TypeHintInjectCWrong');

        // Aqui mesmo com o nome correto, o typehint está do service_b, ou seja,
        // por definição ele irá tentar pelo typehint, acabando que encontra este serviço
        $this->assertNotEquals($this->container['namespace.service_a'], $instance->inject);
        $this->assertSame($this->container['namespace.service_b'], $instance->inject);
    }

    public function testServiceInjectionByTypeHint()
    {
        eval(<<<PHP
class TypeHintInjectD {
    public \$injectHard, \$injectSoft;
    function __construct(SplObjectStorage \$o) {
        \$this->injectHard = \$o;
    }
    function setService(SplObjectStorage \$o) {
        \$this->injectSoft = \$o;
    }
}
PHP
        );

        $instance = $this->injector->createInstance('TypeHintInjectD');

        $this->assertSame($this->container['service_with_long_name'], $instance->injectHard);
        $this->assertSame($this->container['service_with_long_name'], $instance->injectSoft);
    }

    public function testServiceInjectionByUnknownService()
    {
        eval(<<<PHP
class TypeHintService {}
class TypeHintServiceWithDependencies {
    public \$dep;
    function setDep(stdClass \$o) { \$this->dep = \$o; }
}
class TypeHintInjectE {
    public \$injectHard, \$injectSoft;
    function __construct(TypeHintService \$o) {
        \$this->injectHard = \$o;
    }
    function setService(TypeHintServiceWithDependencies \$o) {
        \$this->injectSoft = \$o;
    }
}
PHP
        );

        $instance = $this->injector->createInstance('TypeHintInjectE');

        $o1 = new \TypeHintService();
        $o2 = new \TypeHintServiceWithDependencies();
        $o2->setDep($this->container['namespace.service_b']);

        $this->assertEquals($o1, $instance->injectHard);
        $this->assertEquals($o2, $instance->injectSoft);

        // As instancias de classes criadas pelo proprio injector não são as mesmas,
        // são criadas na hora, ou seja, são instancias diferentes
        $this->assertNotSame($o1, $instance->injectHard);
        $this->assertNotSame($o2, $instance->injectSoft);
    }

    public function testServiceInjectionByTypeHintInterface()
    {
        eval(<<<PHP
class TypeHintInjectF {
    public \$injectHard, \$injectSoft;
    function __construct(ArrayAccess \$o) {
        \$this->injectHard = \$o;
    }
    function setService(ArrayAccess \$o) {
        \$this->injectSoft = \$o;
    }
}
PHP
        );

        $instance = $this->injector->createInstance('TypeHintInjectF');

        $this->assertSame($this->container['namespace.service_a'], $instance->injectHard);
        $this->assertSame($this->container['namespace.service_a'], $instance->injectSoft);

        // Os dois serviços implementam ArrayAccess, porém como o service_a
        // foi definido primeiro, será ele que será injetado ao invés do service_with_long_name
        $this->assertNotSame($this->container['service_with_long_name'], $instance->injectHard);
    }

}
