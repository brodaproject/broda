<?php
namespace Broda\Tests\Core\Controller\Injector\TypeHint;

use Pimple\Container;

class Injectable1 {
    public $params = array();

    function __construct(Container $container) {
        $this->params[] = $container;
    }

    function setContainer(Container $c) {
        $this->params[] = $c;
    }
}

class Injectable2 {
    public $params = array();

    function __construct(\ArrayObject $namespace_serviceX) {
        $this->params[] = $namespace_serviceX;
    }

    function setServiceX(\ArrayObject $namespace_serviceX) {
        $this->params[] = $namespace_serviceX;
    }
}

class Injectable2Multiple {
    public $params = array();

    function __construct(\ArrayObject $namespace_serviceX, \stdClass $namespace_serviceY) {
        $this->params[] = $namespace_serviceX;
        $this->params[] = $namespace_serviceY;
    }

    function setServiceX(\ArrayObject $namespace_serviceX, \stdClass $namespace_serviceY) {
        $this->params[] = $namespace_serviceX;
        $this->params[] = $namespace_serviceY;
    }
}

class Injectable3 {
    public $params = array();

    function __construct(\SplObjectStorage $a) {
        $this->params[] = $a;
    }

    function setService(\SplObjectStorage $a) {
        $this->params[] = $a;
    }
}

class Injectable4 {
    public $params = array();

    function __construct(UnknownService $a) {
        $this->params[] = $a;
    }

    function setService(UnknownServiceWithDependencies $a) {
        $this->params[] = $a;
    }
}

class Injectable5 {
    public $params = array();

    function __construct(\ArrayAccess $a) {
        $this->params[] = $a;
    }

    function setService(\ArrayAccess $a) {
        $this->params[] = $a;
    }
}

interface UnknownServiceInterface {}
class UnknownService implements UnknownServiceInterface {}
class UnknownServiceWithDependencies {
    public $dep;
    function setDep(\stdClass $o) { $this->dep = $o; }
}