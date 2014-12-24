<?php
namespace Broda\Tests\Core\Controller\Injector\PHPStatic;

use Pimple\Container;

class Injectable1 {
    public $params = array();

    function __construct(Container $container) {
        $this->params[] = $container;
    }

    function setContainer(Container $c) {
        $this->params[] = $c;
    }

    static function injectConstructor() {
        return array('CONTAINER');
    }

    function injectContainer() {
        return array('CONTAINER');
    }
}

class Injectable2 {
    public $params = array();

    function __construct(\ArrayObject $a) {
        $this->params[] = $a;
    }

    function setServiceX(\ArrayObject $a) {
        $this->params[] = $a;
    }

    static function injectConstructor() {
        return array('namespace.service_a');
    }

    function injectServiceX() {
        return array('namespace.service_a');
    }
}

class Injectable3 {
    public $params = array();

    function __construct($service1, $service2) {
        $this->params[] = $service1;
        $this->params[] = $service2;
    }

    function setServices($service1, $service2) {
        $this->params[] = $service1;
        $this->params[] = $service2;
    }

    static function injectConstructor() {
        return array('namespace.service_a', 'namespace.service_b');
    }

    function injectServices() {
        return array('namespace.service_a', 'namespace.service_b');
    }
}