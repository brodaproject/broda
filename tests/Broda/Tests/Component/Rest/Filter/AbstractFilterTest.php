<?php
namespace Broda\Tests\Component\Rest\Filter;

use Broda\Component\Rest\Filter\AbstractFilter;
use Broda\Component\Rest\Filter\Param\Column;
use Broda\Component\Rest\Filter\Param\Ordering;
use Broda\Component\Rest\Filter\Param\Searching;
use Broda\Tests\Component\Rest\Mocks\AbstractFilterMock;
use Symfony\Component\HttpFoundation\Request;

/**
 * @group unit
 */
class AbstractFilterTest extends \PHPUnit_Framework_TestCase
{

    /**
     *
     * @var AbstractFilterMock
     */
    private $mockFilter;

    protected function setUp()
    {
        $this->mockFilter = new AbstractFilterMock();
    }

    protected function tearDown()
    {
        AbstractFilter::setDefaultColumns(array());
    }

    public function testDefaults()
    {
        $this->assertEquals(array(), $this->mockFilter->getColumns());
        $this->assertEquals(array(), $this->mockFilter->getColumnSearchs());
        $this->assertEquals(array(), $this->mockFilter->getOrderings());
        $this->assertNull($this->mockFilter->getGlobalSearch());
        $this->assertEquals(0, $this->mockFilter->getFirstResult());
        $this->assertEquals(30, $this->mockFilter->getMaxResults());
    }

    public function testGetColumns()
    {
        $columns = array(new Column('foo'), new Column('bar'));
        $this->mockFilter->setColumns($columns);

        $this->assertEquals($columns, $this->mockFilter->getColumns());
    }

    public function testHasColumn()
    {
        $columns = array(new Column('foo'), new Column('bar'));
        $this->mockFilter->setColumns($columns);

        $this->assertTrue($this->mockFilter->hasColumn('foo'));
        $this->assertTrue($this->mockFilter->hasColumn('bar'));
        $this->assertFalse($this->mockFilter->hasColumn('baz'));
    }

    public function testGetColumn()
    {
        $columns = array($col1 = new Column('foo'), $col2 = new Column('bar'));
        $this->mockFilter->setColumns($columns);

        $this->assertSame($col1, $this->mockFilter->getColumn('foo'));
        $this->assertSame($col2, $this->mockFilter->getColumn('bar'));
        $this->assertNull($this->mockFilter->getColumn('baz'));
    }

    public function testGetColumnSearchs()
    {
        $columns = array(new Searching('foo search', false, 'foo'), new Searching('bar search', false, 'bar'));
        $this->mockFilter->setColumnSearchs($columns);

        $this->assertEquals($columns, $this->mockFilter->getColumnSearchs());
    }

    public function testGetGlobalSearch()
    {
        $this->mockFilter->setGlobalSearch($global = new Searching('global search'));

        $this->assertEquals($global, $this->mockFilter->getGlobalSearch());
        $this->assertEquals($global->getValue(), $this->mockFilter->getGlobalSearch()->getValue());
        $this->assertNull($this->mockFilter->getGlobalSearch()->getColumnName());
    }

    public function testGetOrderings()
    {
        $orders = array(
            new Ordering(new Column('foo')),
            new Ordering(new Column('bar'), 'DESC')
        );
        $this->mockFilter->setOrderings($orders);

        $this->assertEquals($orders, $this->mockFilter->getOrderings());
    }

    public function testGetFirstResult()
    {
        $this->mockFilter->setFirstResult(12);

        $this->assertEquals(12, $this->mockFilter->getFirstResult());
    }

    public function testGetMaxResults()
    {
        $this->mockFilter->setMaxResults(90);

        $this->assertEquals(90, $this->mockFilter->getMaxResults());
    }

    /**
     * @dataProvider detectValuesProvider
     */
    public function testDetectFilterByRequest($request, $expectedFilterClass)
    {
        $filter = AbstractFilter::detectFilterByRequest($request);

        $this->assertInstanceOf($expectedFilterClass, $filter);
    }

    public function detectValuesProvider()
    {
        return array(
            array(Request::create('/', 'GET', array()), 'Broda\Component\Rest\Filter\NullFilter'),
            array(Request::create('/', 'GET', array('s'=>'foo search')), 'Broda\Component\Rest\Filter\DefaultFilter'),
            array(Request::create('/', 'POST', array('draw' => 1)), 'Broda\Component\Rest\Filter\DataTableFilter'),
            array(Request::create('/', 'POST', array('sEcho' => 1)), 'Broda\Component\Rest\Filter\DataTableLegacyFilter'),
        );
    }

    public function testSetDefaultColumns()
    {
        $columns = array(new Column('foo'), new Column('bar'));
        AbstractFilter::setDefaultColumns($columns);

        $filter = new AbstractFilterMock();

        $this->assertEquals($columns, $filter->getColumns());
    }

    /**
     * @dataProvider defaultColumnsProvider
     */
    public function testSetDefaultColumnsShouldConvertValues($columns, $expectedColumns)
    {
        AbstractFilter::setDefaultColumns($columns);

        $filter = new AbstractFilterMock();

        $this->assertEquals($expectedColumns, $filter->getColumns());
    }

    public function defaultColumnsProvider()
    {
        $col1 = array('name' => 'foo', 'data' => 'bar', 'orderable' => true, 'searchable' => false);
        $expCol1 = new Column('foo', 'bar');
        $expCol1->setOrderable(true)->setSearchable(false);

        $col2 = array('name' => 'baz', 'subcolumns' => array('bar', 'foo'));
        $expCol2 = new Column('baz');
        $expCol2->setSubColumns(array(new Column('bar'), new Column('foo')));

        return array(
            array(
                array('foo'),
                array(new Column('foo'))
            ),
            array(
                array('foo', 'bar'),
                array(new Column('foo'), new Column('bar'))
            ),
            array(
                array($col1),
                array($expCol1)
            ),
            array(
                array($col2),
                array($expCol2)
            ),
        );
    }


}

