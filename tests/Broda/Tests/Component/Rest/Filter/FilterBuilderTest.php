<?php

namespace Broda\Tests\Component\Rest\Filter;

use Broda\Component\Rest\Filter\FilterBuilder;
use Broda\Component\Rest\Filter\Param\Column;
use Broda\Component\Rest\Filter\Param\Searching;

class FilterBuilderTest extends FilterBuilderInterfaceTest
{

    /**
     * @var FilterBuilder
     */
    protected $builder;

    public function testAddColumn()
    {
        $this->builder->addColumn('foo');
        $filter = $this->builder->getFilter();

        $this->assertEquals(array($col = new Column('foo')), $filter->getColumns());
        $this->assertEquals($col, $filter->getColumn('foo'));
        $this->assertNull($filter->getColumn('not_exists'));
    }

    public function testAddColumnWithParams()
    {
        $this->builder->addColumn('foo', 'foo.data', false, false);
        $filter = $this->builder->getFilter();

        $col = new Column('foo', 'foo.data');
        $col->setOrderable(false);
        $col->setSearchable(false);

        $this->assertEquals(array($col), $filter->getColumns());
        $this->assertEquals($col, $filter->getColumn('foo'));
    }

    public function testShouldThrowExceptionWithInvalidColumnName()
    {
        $ok = true;

        $invalidNames = array(
            '',
            '1234',
            12543,
        );

        foreach ($invalidNames as $name) {
            try {
                $this->builder->addColumn($name);
                $ok = false;
            } catch (\InvalidArgumentException $e) {
                // ok
            }
        }

        $this->assertTrue($ok);
    }

    public function testSetColumns()
    {
        $this->builder->setColumns(array(
            array('name' => 'foo'),
            array('name' => 'bar'),
            array('name' => 'baz'),
        ));
        $filter = $this->builder->getFilter();

        $this->assertEquals(array(
            $col1 = new Column('foo'),
            $col2 = new Column('bar'),
            $col3 = new Column('baz'),
        ), $filter->getColumns());
        $this->assertEquals($col1, $filter->getColumn('foo'));
        $this->assertEquals($col2, $filter->getColumn('bar'));
        $this->assertEquals($col3, $filter->getColumn('baz'));
        $this->assertNull($filter->getColumn('not_exists'));
    }

    public function testSetColumnsShouldIgnoreInvalids()
    {
        $this->builder->setColumns(array(
            array('name' => 'valid1', 'data' => 'valid.data'),
            array('name' => 'valid2'),
            array('other' => 'invalid'),          // should ignore
            array('name' => 'valid3'),
            'invalid2',                           // should ignore
            true,                                 // should ignore
            array(),                              // should ignore
            array('data' => 'apparently_valid'),  // should ignore
        ));
        $filter = $this->builder->getFilter();

        $this->assertEquals(array(
            $col1 = new Column('valid1', 'valid.data'),
            $col2 = new Column('valid2'),
            $col3 = new Column('valid3'),
        ), $filter->getColumns());
    }

    public function testAddSubColumn()
    {
        $this->builder->addColumn('foo');
        $this->builder->addSubColumn('foo', 'foo.bar');
        $filter = $this->builder->getFilter();

        $col = new Column('foo');
        $col->addSubColumn(new Column('foo.bar'));

        $this->assertEquals(array($col), $filter->getColumns());
        $this->assertEquals($col, $filter->getColumn('foo'));
    }

    /**
     * @expectedException \LogicException
     */
    public function testAddSubColumnForInexistentColumnShouldThrowException()
    {
        $this->builder->addSubColumn('not_exists', 'foo');
    }

    public function testAddColumnSearch()
    {
        $this->builder->addColumn('foo');
        $this->builder->addColumnSearch('foo', 'a b c');
        $filter = $this->builder->getFilter();

        $this->assertEquals(array(new Searching('a b c', 'foo')), $filter->getColumnSearchs());
    }

    public function testAddColumnSearchShouldAddColumnAutomatically()
    {
        $this->builder->addColumnSearch('foo', 'a b c');
        $filter = $this->builder->getFilter();

        $this->assertEquals(new Column('foo'), $filter->getColumn('foo'));
    }

    public function testSetColumnSearchs()
    {
        $this->builder->setColumnSearchs(array(
            array('name' => 'foo', 'searchValue' => 'a b c'),
            array('name' => 'bar', 'searchValue' => 'def'),
        ));
        $filter = $this->builder->getFilter();

        $this->assertEquals(array(
            new Searching('a b c', 'foo'),
            new Searching('def', 'bar'),
        ), $filter->getColumnSearchs());
    }

    public function testSetColumnSearchsShouldIgnoreInvalids()
    {
        $this->builder->setColumnSearchs(array(
            array('name' => 'invalid1', 'data' => 'valid.data'), // should ignore
            array('name' => 'valid', 'searchValue' => 'aaa'),
            array('other' => 'invalid'),          // should ignore
            array('name' => 'invalid'),           // should ignore
            'invalid',                            // should ignore
            true,                                 // should ignore
            array(),                              // should ignore
            array('name' => 'apparently_valid', 'searchValue' => ''),  // should ignore
        ));
        $filter = $this->builder->getFilter();

        $this->assertEquals(array(
            new Searching('aaa', 'valid'),
        ), $filter->getColumnSearchs());
    }

    protected function getBuilder()
    {
        return new FilterBuilder();
    }



}
 