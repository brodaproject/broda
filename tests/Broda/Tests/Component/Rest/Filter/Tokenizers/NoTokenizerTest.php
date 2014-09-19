<?php
namespace Broda\Tests\Component\Rest\Filter\Tokenizers;
use Broda\Component\Rest\Filter\Tokenizers\NoTokenizer;

/**
 * @group unit
 */
class NoTokenizerTest extends BaseTokenizerTest
{

    protected function getTokenizer()
    {
        return new NoTokenizer();
    }

    public function searchsProvider()
    {
        return array(
            // empty strings
            array('', array()),
            array(0, array()),
            array(false, array()),
            // strings
            array(1, array('1')),
            array(new ToStringObj(), array('ToStringObj')),
            array('    ', array('    ')),
            array('umapalavra', array('umapalavra')),
            array('duas palavras', array('duas palavras')),
            array('"uma frase"', array('"uma frase"')),
            array('"uma frase" com palavras', array('"uma frase" com palavras')),
            array("outros\ttipos\rde espacos\na", array("outros\ttipos\rde espacos\na")),
            array("acentuação", array('acentuação')),
            array("chars%para@ignorar*&#$", array('chars%para@ignorar*&#$')),
            array("%$#%$# ignora", array('%$#%$# ignora')),
            array("       ignora            espaços          em     branco     ", array('       ignora            espaços          em     branco     ')),
        );
    }

}

class ToStringObj
{
    function __toString()
    {
        return 'ToStringObj';
    }

}

