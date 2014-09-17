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
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * @ group x
 */
class RestServiceTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var RestService
     */
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
        $this->assertInstanceOf('Broda\Component\Rest\RestService', $this->rest);
    }

    public function testCreateObject()
    {
        $object = $this->rest->createObject('{"prop":"foo"}', 'Broda\Tests\Component\Rest\Fixtures\SerializableObject', 'json');

        $this->assertInstanceOf('Broda\Tests\Component\Rest\Fixtures\SerializableObject', $object);
        $this->assertEquals('foo', $object->prop);
    }

    public function testCreateObjectFromRequest()
    {
        $request = Request::create('/', 'GET', array(), array(), array(), array(), '{"prop":"foo"}');
        $request->headers->set('CONTENT_TYPE', 'application/json');
        $object = $this->rest->createObjectFromRequest($request, 'Broda\Tests\Component\Rest\Fixtures\SerializableObject');

        $this->assertInstanceOf('Broda\Tests\Component\Rest\Fixtures\SerializableObject', $object);
        $this->assertEquals('foo', $object->prop);
    }

    public function testFormatOutputWithObject()
    {
        $object = new SerializableObject();
        $output = $this->rest->formatOutput($object, 'json');

        $this->assertInternalType('string', $output);
        $this->assertJson($output);
        $this->assertEquals('{"prop":"value"}', $output);
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
        $this->assertEquals('[{"prop":"value"},{"prop":"value"},{"prop":"value"}]', $output);
    }

    public function testIncorporateQueryBuider()
    {
        //$qb = new QueryBuilder($this->getMock('Doctrine\ORM\EntityManager'));

    }

    public function testFilteringCriteriaWithNoParams()
    {
        $this->markTestSkipped('Até mudar tudo para o Incorporator, repensar se vai poder pegar o criteria direto');
        return;

        $filter = new NullFilter();

        $criteria = $this->rest->getFilteringCriteria($filter);
        $emptyCriteria = Criteria::create();

        $this->assertInstanceOf('Doctrine\Common\Collections\Criteria', $criteria);
        $this->assertEquals($emptyCriteria->getWhereExpression(), $criteria->getWhereExpression());
        $this->assertEquals($emptyCriteria->getOrderings(),       $criteria->getOrderings());
        //$this->assertNotEquals($emptyCriteria->getMaxResults(),   $criteria->getMaxResults()); // emptyCriteria is null
        //$this->assertEquals($filter->getMaxResults(),             $criteria->getMaxResults()); // NullFilter has a default value for maxresults
        // since 17/09/2014: maxResults is null by default
        $this->assertEquals($emptyCriteria->getMaxResults(),      $criteria->getMaxResults());
    }

    public function testFilteringCriteriaWithOffsetSimple()
    {
        $this->markTestSkipped('Até mudar tudo para o Incorporator, repensar se vai poder pegar o criteria direto');
        return;

        $filter = new DefaultFilter(array('start' => 2));

        $criteria = $this->rest->getFilteringCriteria($filter);
        $emptyCriteria = Criteria::create()
                ->setFirstResult(2);

        $this->assertInstanceOf('Doctrine\Common\Collections\Criteria', $criteria);
        $this->assertEquals($emptyCriteria->getWhereExpression(), $criteria->getWhereExpression());
        $this->assertEquals($emptyCriteria->getOrderings(),       $criteria->getOrderings());
        $this->assertEquals($emptyCriteria->getFirstResult(),     $criteria->getFirstResult());
    }

    public function testFilteringCriteriaWithLimitSimple()
    {
        $this->markTestSkipped('Até mudar tudo para o Incorporator, repensar se vai poder pegar o criteria direto');
        return;

        $filter = new DefaultFilter(array('len' => 3));

        $criteria = $this->rest->getFilteringCriteria($filter);
        $emptyCriteria = Criteria::create()
                ->setMaxResults(3);

        $this->assertInstanceOf('Doctrine\Common\Collections\Criteria', $criteria);
        $this->assertEquals($emptyCriteria->getWhereExpression(), $criteria->getWhereExpression());
        $this->assertEquals($emptyCriteria->getOrderings(),       $criteria->getOrderings());
        $this->assertEquals($emptyCriteria->getMaxResults(),      $criteria->getMaxResults());
    }

    public function testFilteringCriteriaWithOrderingSimple()
    {
        $this->markTestSkipped('Até mudar tudo para o Incorporator, repensar se vai poder pegar o criteria direto');
        return;

        $filter = new DefaultFilter(array('order' => 'name'), array('name', 'age'));

        $criteria = $this->rest->getFilteringCriteria($filter);
        $emptyCriteria = Criteria::create()
                ->orderBy(array('name' => Criteria::ASC));

        $this->assertInstanceOf('Doctrine\Common\Collections\Criteria', $criteria);
        $this->assertEquals($emptyCriteria->getWhereExpression(), $criteria->getWhereExpression());
        $this->assertEquals($emptyCriteria->getOrderings(),       $criteria->getOrderings());
    }

    public function testFilteringCriteriaWithOrderingComplex()
    {
        $this->markTestSkipped('Até mudar tudo para o Incorporator, repensar se vai poder pegar o criteria direto');
        return;

        $filter = new DefaultFilter(array('order' => array('name', 'age')), array('name', 'age'));

        $criteria = $this->rest->getFilteringCriteria($filter);
        $emptyCriteria = Criteria::create()
                ->orderBy(array('name' => Criteria::ASC, 'age' => Criteria::ASC));

        $this->assertInstanceOf('Doctrine\Common\Collections\Criteria', $criteria);
        $this->assertEquals($emptyCriteria->getWhereExpression(), $criteria->getWhereExpression());
        $this->assertEquals($emptyCriteria->getOrderings(),       $criteria->getOrderings());
    }

    /**
     * @dataProvider filteringCriteriaSearchsProvider
     */
    public function testFilteringCriteriaWithSearchs($searchs, $expectedCriteria)
    {
        $this->markTestSkipped('Até mudar tudo para o Incorporator, repensar se vai poder pegar o criteria direto');
        return;

        $columns = array(
            array('name' => 'name'),
            array('name' => 'age'),
            array('name' => 'surname', 'subcolumns' => array('name')),
        );
        $filter = new DefaultFilter($searchs, $columns);

        $criteria = $this->rest->getFilteringCriteria($filter);

        $this->assertInstanceOf('Doctrine\Common\Collections\Criteria', $criteria);
        $this->assertEquals($expectedCriteria->getWhereExpression(), $criteria->getWhereExpression());
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

    /**
     * @expectedException \UnexpectedValueException
     */
    public function testFilterInvalidArgument()
    {
        $this->rest->filter('invalid data', new NullFilter);
    }

    public function filteringCriteriaSearchsProvider()
    {
        return array(
            // global simple
            array(
                array('s' => 'jo'),
                Criteria::create()
                    ->where(
                        Criteria::expr()->orX(
                            Criteria::expr()->contains('name', 'jo'),
                            Criteria::expr()->contains('age', 'jo'),
                            Criteria::expr()->contains('surname', 'jo') // subcolumns ignore globalsearch
                        )
                    )
            ),
            // global complex (token)
            array(
                array('s' => 'jo ma'),
                Criteria::create()
                    ->where(
                        Criteria::expr()->orX(
                            Criteria::expr()->andX( // customizavel
                                Criteria::expr()->contains('name', 'jo'),
                                Criteria::expr()->contains('name', 'ma')
                            ),
                            Criteria::expr()->andX( // customizavel
                                Criteria::expr()->contains('age', 'jo'),
                                Criteria::expr()->contains('age', 'ma')
                            ),
                            Criteria::expr()->andX( // customizavel
                                Criteria::expr()->contains('surname', 'jo'), // subcolumns ignore globalsearch
                                Criteria::expr()->contains('surname', 'ma')
                            )
                        )
                    )
            ),
            // single simple
            array(
                array('name' => 'jo'),
                Criteria::create()
                    ->where(
                        Criteria::expr()->contains('name', 'jo')
                    )
            ),
            // single complex (token)
            array(
                array('name' => 'jo ma'),
                Criteria::create()
                    ->where(
                        Criteria::expr()->andX( // customizavel
                            Criteria::expr()->contains('name', 'jo'),
                            Criteria::expr()->contains('name', 'ma')
                        )
                    )
            ),
            // mixed single simple and complex (token)
            array(
                array('name' => 'jo ma', 'age' => '12'),
                Criteria::create()
                    ->where(
                        Criteria::expr()->andX(
                            Criteria::expr()->andX( // customizavel
                                Criteria::expr()->contains('name', 'jo'),
                                Criteria::expr()->contains('name', 'ma')
                            ),
                            Criteria::expr()->contains('age', '12')
                        )
                    )
            ),
            // mixed single simple and global simple (should intersect)
            array(
                array('s' => 'jo', 'name' => 'ma'),
                Criteria::create()
                    ->where(
                        Criteria::expr()->andX(
                            Criteria::expr()->orX(
                                Criteria::expr()->contains('name', 'jo'),
                                Criteria::expr()->contains('age', 'jo'),
                                Criteria::expr()->contains('surname', 'jo')
                            ),
                            Criteria::expr()->contains('name', 'ma')
                        )
                    )
            ),
            // subcolumns simple
            // should put together the column itself and his subcolumns with OR composition type
            array(
                array('surname' => 'ma'),
                Criteria::create()
                    ->where(
                        Criteria::expr()->orX(
                            Criteria::expr()->contains('surname', 'ma'),
                            Criteria::expr()->contains('name', 'ma')
                        )
                    )
            ),

            // subcolumns simple mixed with column simple
            // should treat subcolumns expression as a normal column, composing with AND
            array(
                array('name' => 'jo', 'surname' => 'ma'),
                Criteria::create()
                    ->where(
                        Criteria::expr()->andX(
                            Criteria::expr()->contains('name', 'jo'),
                            Criteria::expr()->orX(
                                Criteria::expr()->contains('surname', 'ma'),
                                Criteria::expr()->contains('name', 'ma')
                            )
                        )
                    )
            ),
            // subcolumns simple mixed with column simple, with inverted order
            // should consider order
            array(
                array('surname' => 'ma', 'name' => 'jo'),
                Criteria::create()
                    ->where(
                        Criteria::expr()->andX(
                            Criteria::expr()->orX(
                                Criteria::expr()->contains('surname', 'ma'),
                                Criteria::expr()->contains('name', 'ma')
                            ),
                            Criteria::expr()->contains('name', 'jo')
                        )
                    )
            ),

            // subcolumns simple mixed with column simple and global search simple
            // the order is: global first and the order that cames from filter
            array(
                array('name' => 'jo', 'surname' => 'ma', 's' => 'a'),
                Criteria::create()
                    ->where(
                        Criteria::expr()->andX(
                            Criteria::expr()->orX(
                                Criteria::expr()->contains('name', 'a'),
                                Criteria::expr()->contains('age', 'a'),
                                Criteria::expr()->contains('surname', 'a') // global ignores subcolumns
                            ),
                            Criteria::expr()->andX(
                                Criteria::expr()->contains('name', 'jo'),
                                Criteria::expr()->orX(
                                    Criteria::expr()->contains('surname', 'ma'),
                                    Criteria::expr()->contains('name', 'ma')
                                )
                            )
                        )
                    )
            ),
        );
    }

    public function filterProvider()
    {
        AbstractFilter::setDefaultColumns(array('name', 'age'));
        return array(
            array(new DefaultFilter(array('s' => 'jo'))),
            array(new DefaultFilter(array('name' => 'jo'))),
            array(new DataTableFilter(array('search' => array('value' => 'jo'), 'draw' => 1))),
        );
    }


}

