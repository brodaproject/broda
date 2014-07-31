<?php
namespace Broda\Tests\Component\Rest\Filter\Param;

use Broda\Component\Rest\Filter\Param\Column;

/**
 * @group unit
 */
class ColumnTest extends \PHPUnit_Framework_TestCase
{

    public function testInstantiate()
    {
        $col = new Column('foo', 'bar');

        $this->assertInstanceOf('Broda\Component\Rest\Filter\Param\Column', $col);
        $this->assertEquals('foo', $col->getName());
        $this->assertEquals('bar', $col->getData());
        $this->assertTrue($col->getSearchable());
        $this->assertTrue($col->getOrderable());
        $this->assertEquals(array(), $col->getSubColumns());
    }

    public function testInstantiateDataDefaultToName()
    {
        $col = new Column('foo');

        $this->assertEquals('foo', $col->getName());
        $this->assertEquals('foo', $col->getData());
    }

    public function testSetName()
    {
        $col = new Column('foo', 'bar');
        $col->setName('baz');

        $this->assertEquals('baz', $col->getName());
        $this->assertEquals('bar', $col->getData());
    }

    public function testSetNameShouldNotChangeData()
    {
        $col = new Column('foo');
        $col->setName('bar');

        $this->assertEquals('bar', $col->getName());
        $this->assertEquals('foo', $col->getData());
    }

    public function testSetData()
    {
        $col = new Column('foo');
        $col->setData('bar');

        $this->assertEquals('foo', $col->getName());
        $this->assertEquals('bar', $col->getData());
    }

    public function testSetSearchable()
    {
        $col = new Column('foo');
        $col->setSearchable(false);

        $this->assertFalse($col->getSearchable());
    }

    public function testSetOrderable()
    {
        $col = new Column('foo');
        $col->setOrderable(false);

        $this->assertFalse($col->getOrderable());
    }

    public function testAddSubcolumns()
    {
        $col = new Column('foo');
        $col->addSubColumn($scol1 = new Column('bar'));
        $col->addSubColumn($scol2 = new Column('baz'));

        $this->assertEquals(array($scol1, $scol2), $col->getSubColumns());
    }

    public function testRemoveSubcolumns()
    {
        $col = new Column('foo');
        $col->addSubColumn($scol1 = new Column('bar'));
        $col->addSubColumn($scol2 = new Column('baz'));
        $col->removeSubColumn($scol1);

        $this->assertEquals(array($scol2), $col->getSubColumns());
    }

    public function testSetSubcolumns()
    {
        $col = new Column('foo');
        $scols = array(new Column('bar'), new Column('bar'));
        $col->setSubColumns($scols);

        $this->assertEquals($scols, $col->getSubColumns());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testSetSubcolumnsMustBeAnArrayOfColumns()
    {
        $col = new Column('foo');
        $scols = array('a', 'b');
        $col->setSubColumns($scols);
    }

}

