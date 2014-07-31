<?php

namespace Broda\Component\Rest\Filter\Tokenizers;

/**
 * Classe BasicTokenizer
 *
 * @author raphael
 */
class BasicTokenizer implements TokenizerInterface
{

    public $ignoreChars = '/[^\w\s"\']/u';
    public $quotes      = array('"', "'");
    public $whitespaces = array(' ', "\s", "\t", "\r", "\n", "\0");

    private $quotings = array();
    private $words = array();

    public function tokenize($string)
    {
        // clean string
        $string = preg_replace($this->ignoreChars, '', $string);

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

                $this->addWord($word);

            } elseif (in_array($char, $this->whitespaces) && !$this->isAlreadyQuoting()) {
                // spacing
                $this->addWord($word);
                
            } else {
                // else, construct the word
                $word .= $char;
            }
        }
        $this->addWord($word);

        return $this->words;
    }

    private function setQuoting($quote)
    {
        $this->quotings[$quote] = true;
    }

    private function unsetQuoting($quote)
    {
        unset($this->quotings[$quote]);
    }

    private function isQuoting($quote)
    {
        return isset($this->quotings[$quote]);
    }

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

    private function addWord(&$word)
    {
        if ($word) {
            $this->words[] = $word;
        }
        $word = '';
    }

}
