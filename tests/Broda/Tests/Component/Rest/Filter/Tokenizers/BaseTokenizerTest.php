<?php

namespace Broda\Tests\Component\Rest\Filter\Tokenizers;

use Broda\Component\Rest\Filter\Tokenizers\TokenizerInterface;

abstract class BaseTokenizerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var TokenizerInterface
     */
    protected $tokenizer;

    protected function setUp()
    {
        $this->tokenizer = $this->getTokenizer();
    }

    /**
     * @return TokenizerInterface
     */
    abstract protected function getTokenizer();

    /**
     * @dataProvider searchsProvider
     */
    public function testTokenize($search, $expectedWords)
    {
        $words = $this->tokenizer->tokenize($search);

        $this->assertEquals($expectedWords, $words);
    }

    abstract public function searchsProvider();
}
 