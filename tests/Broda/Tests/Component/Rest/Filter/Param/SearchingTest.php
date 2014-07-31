<?php
namespace Broda\Tests\Component\Rest\Filter\Param;

use Broda\Component\Rest\Filter\Param\Searching;

/**
 * @group unit
 */
class SearchingTest extends \PHPUnit_Framework_TestCase
{

    public function testInstantiate()
    {
        $sch = new Searching('foo bar', true, 'baz');

        $this->assertInstanceOf('Broda\Component\Rest\Filter\Param\Searching', $sch);
        $this->assertEquals('foo bar', $sch->getValue());
        $this->assertTrue($sch->getRegex());
        $this->assertEquals('baz', $sch->getColumnName());
        $this->assertInstanceOf('Broda\Component\Rest\Filter\Tokenizers\BasicTokenizer', $sch->getTokenizer());
        $this->assertEquals(array('foo', 'bar'), $sch->getTokens());
    }

    public function testInstantiateDefault()
    {
        $sch = new Searching('foo bar');

        $this->assertFalse($sch->getRegex()); // TODO pensar se é melhor deixar false por padrão mesmo ou não
        $this->assertNull($sch->getColumnName());
        $this->assertInstanceOf('Broda\Component\Rest\Filter\Tokenizers\BasicTokenizer', $sch->getTokenizer());
        $this->assertEquals(array('foo', 'bar'), $sch->getTokens());
    }

    public function testSetValue()
    {
        $sch = new Searching('foo bar');
        $sch->setValue('baz baz');

        $this->assertEquals('baz baz', $sch->getValue());
    }

    public function testSetRegex()
    {
        $sch = new Searching('foo bar');
        $sch->setRegex(true);

        $this->assertTrue($sch->getRegex());

        $sch->setRegex(false);

        $this->assertFalse($sch->getRegex());
    }

    public function testSetColumnName()
    {
        $sch = new Searching('foo bar');
        $sch->setColumnName('baz');

        $this->assertEquals('baz', $sch->getColumnName());
    }

    public function testSetTokenizer()
    {
        $tokenizer = new \Broda\Component\Rest\Filter\Tokenizers\BasicTokenizer();
        $sch = new Searching('foo bar');

        $this->assertNotSame($tokenizer, $sch->getTokenizer());

        $sch->setTokenizer($tokenizer);

        $this->assertSame($tokenizer, $sch->getTokenizer());
    }

    public function testGetTokenizerShouldReturnSameDefaultTokenizerAfterGet()
    {
        $sch = new Searching('foo bar');
        $defaultTokenizer = $sch->getTokenizer();

        $this->assertSame($defaultTokenizer, $sch->getTokenizer());
        $this->assertSame($defaultTokenizer, $sch->getTokenizer());

    }

    public function testGetTokensShouldTokenize()
    {
        $tokenizer = new \Broda\Component\Rest\Filter\Tokenizers\BasicTokenizer();
        $sch = new Searching('foo bar');
        $sch->setTokenizer($tokenizer);

        $this->assertEquals($tokenizer->tokenize('foo bar'), $sch->getTokens());

        // tokens are gerenated in time, so change the value also change the tokens
        $sch->setValue('baz baz "bar foo"');

        $this->assertEquals($tokenizer->tokenize('baz baz "bar foo"'), $sch->getTokens());
    }

}

