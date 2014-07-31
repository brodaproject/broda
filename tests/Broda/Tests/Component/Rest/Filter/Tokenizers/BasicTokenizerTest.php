<?php
namespace Broda\Tests\Component\Rest\Filter\Tokenizers;

/**
 * @group unit
 */
class BasicTokenizerTest extends \PHPUnit_Framework_TestCase
{

    private $tokenizer;

    protected function setUp()
    {
        $this->tokenizer = new \Broda\Component\Rest\Filter\Tokenizers\BasicTokenizer();
    }

    /**
     * @dataProvider searchsProvider
     */
    public function testTokenize($search, $expectedWords)
    {
        $words = $this->tokenizer->tokenize($search);

        $this->assertEquals($expectedWords, $words);
    }

    /**
     * @dataProvider searchsProviderCustomQuotes
     */
    public function testTokenizeWithCustomQuotes($search, $expectedWords)
    {
        $this->tokenizer->quotes = array('a', 'B');
        $words = $this->tokenizer->tokenize($search);

        $this->assertEquals($expectedWords, $words);
    }

    /**
     * @dataProvider searchsProviderCustomWhitespaces
     */
    public function testTokenizeWithCustomWhitespaces($search, $expectedWords)
    {
        $this->tokenizer->whitespaces = array('a', 'B');
        $words = $this->tokenizer->tokenize($search);

        $this->assertEquals($expectedWords, $words);
    }

    public function searchsProvider()
    {
        return array(
            array('', array()),
            array('    ', array()),
            array('umapalavra', array('umapalavra')),
            array('duas palavras', array('duas', 'palavras')),
            array('"uma frase"', array('uma frase')),
            array('"uma frase" com palavras', array('uma frase', 'com', 'palavras')),
            array('palavra depois "uma frase" com palavras', array('palavra', 'depois', 'uma frase', 'com', 'palavras')),
            array('grudar"quotes abc"', array('grudar', 'quotes abc')),
            array("outros\ttipos\rde espacos\na", array('outros', 'tipos', 'de', 'espacos', 'a')),
            array("acentuação", array('acentuação')),
            array("chars%para@ignorar*&#$", array('charsparaignorar')),
            array("%$#%$# ignora", array('ignora')),
            array("       ignora            espaços          em     branco     ", array('ignora', 'espaços', 'em', 'branco')),
            array("    '     não    ignora    espaços    de   quotes'    ", array('     não    ignora    espaços    de   quotes')),
            array("com 'quotes \"dentro de outro\"' abc", array('com', 'quotes "dentro de outro"', 'abc')),
            array('com "quotes \'dentro de outro\'" abc', array('com', "quotes 'dentro de outro'", 'abc')),
            array('com "quote quebrado', array('com', 'quote quebrado')),
            array('com "quote \' misturado quebrado', array('com', 'quote \' misturado quebrado')),
            array('com "quote \'complexo" quebrado\' e mal formado', array('com', 'quote \'complexo', 'quebrado', ' e mal formado')),
            //     <>< . . . .>< .> < ><
            array('"""\'\'\'\'""\'"\'\'"', array("''''", "'")),
        );
    }

    public function searchsProviderCustomQuotes()
    {
        // quotes: a, B
        // legenda: '|' whitespace, '<' quote inicio, '>' quote fim, '.' frase do quote
        return array(
            //     <..>|  <..>
            array('auma Frasea', array('um', 'Fr', 'se')),
            //     <...........>
            array('Boutra FraseB', array('outra Frase')),
            //     <...........>|  |  |  <.....>
            array('a123 456 789a 10 11 12B13 14B', array('123 456 789', '10', '11', '12', '13 14')),
            //     <........>
            array('a123B456Ba', array('123B456B')),
            //     <><...><.><><.
            array('aaaBBBaaBaBBBa', array('BBB', 'B', 'a')),
        );
    }

    public function searchsProviderCustomWhitespaces()
    {
        // whitespaces: a, B
        // lagenda: '|' whitespace
        return array(
            //     |     |
            array('abcdefabc', array('bcdef', 'bc')),
            //               | |
            array('não mistura acentos', array('não mistur', ' ', 'centos')),
            //             |                         |||
            array('minuscula e MAIUSCULA tem diferençaBB', array('minuscul', ' e MAIUSCULA tem diferenç')),
            //                           |               |
            array('   outros tipos de espaço são considerados  ', array('   outros tipos de esp', 'ço são consider', 'dos  ')),
            //                     |         |    ||
            array('"quotes consideram whitespaces aB"', array('quotes consideram whitespaces aB')),
            //     |||||||||||||||
            array('BBBaaBBaaBBBBaa', array()),

        );
    }

}

