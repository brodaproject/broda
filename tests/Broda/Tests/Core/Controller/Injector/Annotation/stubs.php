<?php
namespace Broda\Tests\Core\Controller\Injector\Annotation;

use Broda\Core\Controller\Annotations\Inject;
use Pimple\Container;

class Injectable1 {
    public $params = array();

    /**
     * @Inject("CONTAINER")
     */
    function __construct(Container $container) {
        $this->params[] = $container;
    }

    /**
     * @Inject("CONTAINER")
     */
    function setContainer(Container $c) {
        $this->params[] = $c;
    }
}

class Injectable2 {
    public $params = array();

    /**
     * @Inject("namespace.service_a")
     */
    function __construct(\ArrayObject $x) {
        $this->params[] = $x;
    }

    /**
     * @Inject("namespace.service_a")
     */
    function setServiceX(\ArrayObject $x) {
        $this->params[] = $x;
    }

}

class Injectable3 {
    public $params = array();

    /**
     * @Inject("namespace.service_a")
     * @Inject("namespace.service_b")
     */
    function __construct($service1, $service2) {
        $this->params[] = $service1;
        $this->params[] = $service2;
    }

    /**
     * @Inject("namespace.service_a")
     * @Inject("namespace.service_b")
     */
    function setServices($service1, $service2) {
        $this->params[] = $service1;
        $this->params[] = $service2;
    }
}