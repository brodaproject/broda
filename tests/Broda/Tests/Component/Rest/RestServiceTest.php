<?php
namespace Broda\Tests\Component\Rest;

use Broda\Component\Rest\Filter\AbstractFilter;
use Broda\Component\Rest\Filter\DataTableFilter;
use Broda\Component\Rest\Filter\DefaultFilter;
use Broda\Component\Rest\Filter\FilterInterface;
use Broda\Component\Rest\Filter\NullFilter;
use Broda\Component\Rest\Filter\TotalizableInterface;
use Broda\Component\Rest\RestService;
use Broda\Tests\Component\Rest\Fixtures\SerializableObject;
use Doctrine\Common\Collections\Criteria;

/**
 * @ group x
 */
class RestServiceTest extends \PHPUnit_Framework_TestCase
{

    private $rest;
    private $data;

    protected function setUp()
    {
        $this->rest = new RestService();
        $this->data = array(
            array('name' => 'joao', 'age' => 15, 'surname' => 'silva'),
            array('name' => 'johannes', 'age' => 29, 'surname' => 'fernandez'),
            array('name' => 'maria', 'age' => 30, 'surname' => 'silva'),
            array('name' => 'carlos', 'age' => 60, 'surname' => 'del castilho')
        );
    }

    public function testInstanciate()
    {
        $this->assertInstanceOf("Broda\Component\Rest\RestService", $this->rest);
    }

    public function testCreateObject()
    {
        $object = $this->rest->createObject('{"prop":"foo"}', 'Broda\Tests\Component\Rest\Fixtures\SerializableObject', 'json');

        $this->assertInstanceOf('Broda\Tests\Component\Rest\Fixtures\SerializableObject', $object);
        $this->assertEquals('foo', $object->prop);
    }

    public function testFormatOutputWithObject()
    {
        $object = new SerializableObject();
        $output = $this->rest->formatOutput($object, 'json');

        $this->assertInternalType('string', $output);
        $this->assertJson($output);
        $this->assertEquals($output, '{"prop":"value"}');
    }

    public function testFormatOutputWithArrayOfObjects()
    {
        $object = array(
            new SerializableObject(),
            new SerializableObject(),
            new SerializableObject(),
        );
        $output = $this->rest->formatOutput($object, 'json');

        $this->assertInternalType('string', $output);
        $this->assertJson($output);
        $this->assertEquals($output, '[{"prop":"value"},{"prop":"value"},{"prop":"value"}]');
    }

    public function testFilteringCriteriaWithNoParams()
    {
        $filter = new NullFilter();

        $criteria = $this->rest->getFilteringCriteria($filter);
        $emptyCriteria = Criteria::create();

        $this->assertInstanceOf('Doctrine\Common\Collections\Criteria', $criteria);
        $this->assertEquals($criteria->getWhereExpression(), $emptyCriteria->getWhereExpression());
        $this->assertEquals($criteria->getOrderings(), $emptyCriteria->getOrderings());
        $this->assertNotEquals($criteria->getMaxResults(), $emptyCriteria->getMaxResults()); // emptyCriteria is null
        $this->assertEquals($criteria->getMaxResults(), $filter->getMaxResults()); // NullFilter has a default value for maxresults
    }

    public function testFilteringCriteriaWithOffsetSimple()
    {
        $filter = new DefaultFilter(array('start' => 2));

        $criteria = $this->rest->getFilteringCriteria($filter);
        $emptyCriteria = Criteria::create()
                ->setFirstResult(2);

        $this->assertInstanceOf('Doctrine\Common\Collections\Criteria', $criteria);
        $this->assertEquals($criteria->getWhereExpression(), $emptyCriteria->getWhereExpression());
        $this->assertEquals($criteria->getOrderings(), $emptyCriteria->getOrderings());
        $this->assertEquals($criteria->getFirstResult(), $emptyCriteria->getFirstResult());
    }

    public function testFilteringCriteriaWithLimitSimple()
    {
        $filter = new DefaultFilter(array('len' => 3));

        $criteria = $this->rest->getFilteringCriteria($filter);
        $emptyCriteria = Criteria::create()
                ->setMaxResults(3);

        $this->assertInstanceOf('Doctrine\Common\Collections\Criteria', $criteria);
        $this->assertEquals($criteria->getWhereExpression(), $emptyCriteria->getWhereExpression());
        $this->assertEquals($criteria->getOrderings(), $emptyCriteria->getOrderings());
        $this->assertEquals($criteria->getMaxResults(), $emptyCriteria->getMaxResults());
    }

    public function testFilteringCriteriaWithSearchGlobalSimple()
    {
        $filter = new DefaultFilter(array('s' => 'jo'), array('name', 'age'));

        $criteria = $this->rest->getFilteringCriteria($filter);
        $emptyCriteria = Criteria::create()
                ->where(
                        Criteria::expr()->orX(
                            Criteria::expr()->contains('name', 'jo'),
                            Criteria::expr()->contains('age', 'jo')
                        )
                );

        $this->assertInstanceOf('Doctrine\Common\Collections\Criteria', $criteria);
        $this->assertEquals($criteria->getWhereExpression(), $emptyCriteria->getWhereExpression());
        $this->assertEquals($criteria->getOrderings(), $emptyCriteria->getOrderings());
    }

    /**
     * @todo não 'encavalar' mais expressoes iguais (deixa-las 'flat', usando CompositeExpression::getExpressionList())
     */
    public function testFilteringCriteriaWithSearchGlobalComplex()
    {
        $filter = new DefaultFilter(array('s' => 'jo'), array('name', 'age', 'surname'));

        $criteria = $this->rest->getFilteringCriteria($filter);
        $emptyCriteria = Criteria::create()
                ->where(
                        Criteria::expr()->orX(
                            Criteria::expr()->orX( // as expressoes iguais ficam 'encavaladas'
                                Criteria::expr()->contains('name', 'jo'),
                                Criteria::expr()->contains('age', 'jo')
                            ),
                            Criteria::expr()->contains('surname', 'jo')
                        )
                );

        $this->assertInstanceOf('Doctrine\Common\Collections\Criteria', $criteria);
        $this->assertEquals($criteria->getWhereExpression(), $emptyCriteria->getWhereExpression());
        $this->assertEquals($criteria->getOrderings(), $emptyCriteria->getOrderings());
    }

    public function testFilteringCriteriaWithSearchGlobalWithTokenSimple()
    {
        $filter = new DefaultFilter(array('s' => 'jo ma'), array('name', 'age'));

        $criteria = $this->rest->getFilteringCriteria($filter);
        $emptyCriteria = Criteria::create()
                ->where(
                        Criteria::expr()->orX(
                            Criteria::expr()->andX(
                                Criteria::expr()->contains('name', 'jo'),
                                Criteria::expr()->contains('name', 'ma')
                            ),
                            Criteria::expr()->andX(
                                Criteria::expr()->contains('age', 'jo'),
                                Criteria::expr()->contains('age', 'ma')
                            )
                        )
                );

        $this->assertInstanceOf('Doctrine\Common\Collections\Criteria', $criteria);
        $this->assertEquals($criteria->getWhereExpression(), $emptyCriteria->getWhereExpression());
        $this->assertEquals($criteria->getOrderings(), $emptyCriteria->getOrderings());
    }

    /**
     * @todo não 'encavalar' mais expressoes iguais (deixa-las 'flat', usando CompositeExpression::getExpressionList())
     */
    public function testFilteringCriteriaWithSearchGlobalWithTokenComplex()
    {
        $filter = new DefaultFilter(array('s' => 'jo ma'), array('name', 'age', 'surname'));

        $criteria = $this->rest->getFilteringCriteria($filter);
        $emptyCriteria = Criteria::create()
                ->where(
                        Criteria::expr()->orX(
                            Criteria::expr()->orX( // as expressoes iguais ficam 'encavaladas'
                                Criteria::expr()->andX(
                                    Criteria::expr()->contains('name', 'jo'),
                                    Criteria::expr()->contains('name', 'ma')
                                ),
                                Criteria::expr()->andX(
                                    Criteria::expr()->contains('age', 'jo'),
                                    Criteria::expr()->contains('age', 'ma')
                                )
                            ),
                            Criteria::expr()->andX(
                                Criteria::expr()->contains('surname', 'jo'),
                                Criteria::expr()->contains('surname', 'ma')
                            )
                        )
                );

        $this->assertInstanceOf('Doctrine\Common\Collections\Criteria', $criteria);
        $this->assertEquals($criteria->getWhereExpression(), $emptyCriteria->getWhereExpression());
        $this->assertEquals($criteria->getOrderings(), $emptyCriteria->getOrderings());
    }

    /**
     * @dataProvider filterProvider
     *
     * @param FilterInterface $filter
     */
    public function testFilter($filter)
    {
        $r = $this->rest->filter($this->data, $filter);
        $flat = $this->rest->formatOutput($r, 'json');

        $this->assertContains('joao', $flat);
        $this->assertContains('johannes', $flat);
        $this->assertNotContains('maria', $flat);
        $this->assertNotContains('carlos', $flat);
        if ($filter instanceof TotalizableInterface) {
            $this->assertContains('2', $flat);
        }
    }

    public function filterProvider()
    {
        AbstractFilter::setDefaultColumns(array('name', 'age'));
        return array(
            array(new DefaultFilter(array('s' => 'jo'))),
            array(new DataTableFilter(array('search' => 'jo', 'draw' => 1))),
        );
    }

}

