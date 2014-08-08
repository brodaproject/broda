<?php
namespace Broda\Tests\Framework\Model\Filter;

/**
 * @group unit
 */
class DeletableFilterTest extends \PHPUnit_Framework_TestCase
{

    public function testInstantiate()
    {
        $model = new DeletableModel();

        $this->assertInstanceOf('Broda\Framework\Model\DeletableInterface', $model);
        $this->assertEquals('deletado', $model->getDeletableField());
    }

}

class DeletableModel implements \Broda\Framework\Model\DeletableInterface
{
    public static function getDeletableField()
    {
        return 'deletado';
    }
}
