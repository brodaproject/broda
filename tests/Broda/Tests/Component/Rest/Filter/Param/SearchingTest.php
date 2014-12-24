<?php
namespace Broda\Tests\Component\Rest\Filter\Param;

use Broda\Component\Rest\Filter\Param\Searching;
use Broda\Component\Rest\Filter\Tokenizers\BasicTokenizer;
use Broda\Tests\TestCase;

/**
 * @group unit
 */
class SearchingTest extends TestCase
{

    public function testInstantiate()
    {
        $sch = new Searching('foo bar', 'baz');

        $this->assertInstanceOf('Broda\Component\Rest\Filter\Param\Searching', $sch);
        $this->assertEquals('foo bar', $sch->getValue());
        $this->assertFalse($sch->getRegex());
        $this->assertFalse($sch->isExactly());
        $this->assertTrue($sch->isTokenizable());
        $this->assertEquals('AND', $sch->getTokenSeparator());
        $this->assertEquals('baz', $sch->getColumnName());
        $this->assertInstanceOf('Broda\Component\Rest\Filter\Tokenizers\BasicTokenizer', $sch->getTokenizer());
        $this->assertEquals(array('foo', 'bar'), $sch->getTokens());
    }

    public function testInstantiateDefault()
    {
        $sch = new Searching('foo bar');

        $this->assertFalse($sch->getRegex()); // é melhor deixar FALSE por padrão pois o processamento é pesado com regex
        $this->assertFalse($sch->isExactly());
        $this->assertTrue($sch->isTokenizable());
        $this->assertNull($sch->getColumnName());
        $this->assertEquals('AND', $sch->getTokenSeparator());
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

    public function testSetExactly()
    {
        $sch = new Searching('foo bar');
        $sch->setExactly(true);

        $this->assertTrue($sch->isExactly());

        $sch->setExactly(false);

        $this->assertFalse($sch->isExactly());
    }

    public function testSetTokenizable()
    {
        $sch = new Searching('foo bar');
        $sch->setTokenizable(true);

        $this->assertTrue($sch->isTokenizable());

        $sch->setTokenizable(false);

        $this->assertFalse($sch->isTokenizable());
    }

    public function testSetTokenSeparator()
    {
        $sch = new Searching('foo bar');
        $sch->setTokenSeparator('OR');

        $this->assertEquals('OR', $sch->getTokenSeparator());
    }

    public function testSetColumnName()
    {
        $sch = new Searching('foo bar');
        $sch->setColumnName('baz');

        $this->assertEquals('baz', $sch->getColumnName());
    }

    public function testSetTokenizer()
    {
        $tokenizer = new BasicTokenizer();
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
        $tokenizer = new BasicTokenizer();
        $sch = new Searching('foo bar');
        $sch->setTokenizer($tokenizer);

        $this->assertEquals($tokenizer->tokenize('foo bar'), $sch->getTokens());

        // tokens are gerenated in time, so change the value also change the tokens
        $sch->setValue('baz baz "bar foo"');

        $this->assertEquals($tokenizer->tokenize('baz baz "bar foo"'), $sch->getTokens());
    }

}

