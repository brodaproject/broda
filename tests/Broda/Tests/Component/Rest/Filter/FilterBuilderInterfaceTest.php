<?php

namespace Broda\Tests\Component\Rest\Filter;

use Broda\Component\Rest\Filter\FilterBuilderInterface;

abstract class FilterBuilderInterfaceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var FilterBuilderInterface
     */
    protected $builder;

    protected function setUp()
    {
        $this->builder = $this->getBuilder();
    }

    /**
     * @return FilterBuilderInterface
     */
    abstract protected function getBuilder();

    public function testAllMethodsShouldReturnSelf()
    {
        $refl = new \ReflectionClass($this->builder);
        $methods = $refl->getMethods();

        foreach ($methods as $method) {
            if ($method->getName() === 'getFilter'
                || $method->isStatic()
                || !$method->isPublic()) {
                continue;
            }

            $arguments = array();
            $params = $method->getParameters();
            foreach ($params as $param) {
                if ($param->isOptional()) continue;

                $arguments[] = $param->isArray() ? array() : 'stub';
            }

            try {
                $return = call_user_func_array(array($this->builder, $method->getName()), $arguments);

                $this->assertInstanceOf('Broda\Component\Rest\Filter\FilterBuilderInterface', $return);
                $this->assertSame($this->builder, $return);
            } catch (\Exception $e) {
                $this->markTestIncomplete('Não foi possivel chamar o método '.
                    $method->getName().' como stub');
            }

        }
    }

    public function testGetFilter()
    {
        $filter = $this->builder->getFilter();

        $this->assertInstanceOf('Broda\Component\Rest\Filter\FilterInterface', $filter);
    }

}
 