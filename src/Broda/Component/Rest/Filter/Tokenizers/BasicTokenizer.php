<?php

namespace Broda\Component\Rest\Filter\Tokenizers;

/**
 * Cria tokens para strings separadas por espaços. Se
 * a string estiver envolvida por aspas ou apóstrofos, o token
 * considera os espaços.
 *
 * É bem parecido com a tokenização da pesquisa do Google.
 *
 * Ignora caracteres que não são letras, números, espaços ou aspas.
 *
 * NOTA: só é suportado strings UTF-8
 *
 * TODO: suportar outros charsets fora utf-8
 *
 * @author raphael
 */
class BasicTokenizer implements TokenizerInterface
{
    const IGNORE_CHARS_REGEX = '/[^%s]/u';

    public $allowedChars = array("\\w", "\\s");
    public $quotes      = array('"', "'");
    public $whitespaces = array(' ', "\t", "\r", "\n");

    private $quotings = array();
    private $words = array();

    /**
     * {@inheritdoc}
     */
    public function tokenize($string)
    {
        // limpa string
        $allowedChars = ''
            . implode('', $this->allowedChars)
            . implode('', $this->quotes)
            . implode('', $this->whitespaces);

        $string = preg_replace(sprintf(self::IGNORE_CHARS_REGEX, $allowedChars), '', $string);

        $word = '';
        $quote = false;
        $this->words = array();

        for($i = 0,$len = strlen($string); $i < $len; $i++) {
            $char = $string[$i];
            if (false !== ($quote = array_search($char, $this->quotes)) && !$this->isAlreadyQuoting($quote)) {
                // quoting
                if ($this->isQuoting($quote)) {
                    $this->unsetQuoting($quote);
                } else {
                    $this->setQuoting($quote);
                }

                // adiciona a ultima palavra formada, ou por
                // fechar um quote ou por abrir um quote
                $this->addWord($word);

            } elseif (in_array($char, $this->whitespaces) && !$this->isAlreadyQuoting()) {
                // espaço
                $this->addWord($word);
                
            } else {
                // senão, constroi a palavra
                $word .= $char;
            }
        }
        $this->addWord($word);

        return $this->words;
    }

    /**
     * @internal
     * Define se está em modo "aspas" ou não
     */
    private function setQuoting($quote)
    {
        $this->quotings[$quote] = true;
    }

    /**
     * @internal
     * Define que não está mais em modo "aspas"
     */
    private function unsetQuoting($quote)
    {
        unset($this->quotings[$quote]);
    }

    /**
     * @internal
     * Verifica se está em modo "aspas"
     */
    private function isQuoting($quote)
    {
        return isset($this->quotings[$quote]);
    }

    /**
     * @internal
     * Verifica se está em modo "aspas" em qualquer aspas correntes,
     * a menos que $ignoreQuote seja passado (ignora aquela aspa específica).
     */
    private function isAlreadyQuoting($ignoreQuote = null)
    {
        foreach ($this->quotings as $quote => $v) {
            if ($quote === $ignoreQuote) continue;
            if ($v === true) {
                return true;
            }
        }
        return false;
    }

    /**
     * @internal
     * Adiciona uma palavra no retorno do tokenizer
     */
    private function addWord(&$word)
    {
        if ($word) {
            $this->words[] = $word;
        }
        $word = '';
    }

}
