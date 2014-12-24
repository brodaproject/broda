<?php
namespace Broda\Tests\Component\Rest\Filter\Param;

use Broda\Component\Rest\Filter\Param\Column;
use Broda\Component\Rest\Filter\Param\Ordering;
use Broda\Tests\TestCase;
use Doctrine\Common\Collections\Criteria;

/**
 * @group unit
 */
class OrderingTest extends TestCase
{

    public function testInstantiate()
    {
        $ord = new Ordering($col = new Column('foo'), Criteria::DESC);

        $this->assertInstanceOf('Broda\Component\Rest\Filter\Param\Ordering', $ord);
        $this->assertEquals($col, $ord->getColumn());
        $this->assertEquals(Criteria::DESC, $ord->getDir());
    }

    public function testInstantiateDefault()
    {
        $ord = new Ordering(new Column('foo'));

        $this->assertEquals(Criteria::ASC, $ord->getDir());
    }

    public function testSetColumn()
    {
        $ord = new Ordering(new Column('foo'));
        $ord->setColumn($col = new Column('bar'));

        $this->assertSame($col, $ord->getColumn());
        $this->assertEquals('bar', $ord->getColumn()->getName());
    }

    /**
     * @ expectedException \Exception
     * /
    public function testSetColumnRequiresAColumn()
    {
        $ord = new Ordering(new Column('foo'));
        $ord->setColumn(null);
    }

    /**
     * @ expectedException \Exception
     * /
    public function testInstantiateRequiresAColumn()
    {
        new Ordering();
    }*/

    public function testSetDir()
    {
        $ord = new Ordering(new Column('foo'));
        $ord->setDir(Criteria::DESC);

        $this->assertEquals(Criteria::DESC, $ord->getDir());

        $ord->setDir(Criteria::ASC);

        $this->assertEquals(Criteria::ASC, $ord->getDir());
    }

    /**
     * @dataProvider dirsProvider
     */
    public function testSetDirShouldConvertIrregularValues($dir, $expectedDir)
    {
        $ord = new Ordering(new Column('foo'));
        $ord->setDir($dir);

        $this->assertEquals($expectedDir, $ord->getDir());
    }

    public function dirsProvider()
    {
        return array(
            array(Criteria::DESC, Criteria::DESC),
            array(Criteria::ASC,  Criteria::ASC),
            array('desc',         Criteria::DESC),
            array('dEsC',         Criteria::DESC),
            array('DESC',         Criteria::DESC),
            array('asc',          Criteria::ASC),
            array(' desc',        Criteria::ASC),
            array('invalid',      Criteria::ASC),
            array('',             Criteria::ASC),
            array(null,           Criteria::ASC),
            array(-1,             Criteria::DESC),
            array(-2,             Criteria::DESC),
            array('-1',           Criteria::DESC),
            array(1,              Criteria::ASC),
            array(0,              Criteria::ASC),
        );
    }

}

