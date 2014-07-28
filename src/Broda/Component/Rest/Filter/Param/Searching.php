<?php

namespace Broda\Component\Rest\Filter\Param;

use Broda\Component\Rest\Filter\Tokenizers\BasicTokenizer;
use Broda\Component\Rest\Filter\Tokenizers\TokenizerInterface;

/**
 * Classe Searching
 *
 * @author raphael
 */
class Searching
{

    protected $value;
    protected $regex = false;
    protected $columnName = null;
    /**
     *
     * @var TokenizerInterface
     */
    protected $tokenizer;

    public function __construct($value, $regex = false, $columnName = null)
    {
        $this->value = $value;
        $this->regex = $regex;
        $this->columnName = $columnName;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function getRegex()
    {
        return $this->regex;
    }

    public function getColumnName()
    {
        return $this->columnName;
    }

    public function getTokens()
    {
        return $this->getTokenizer()->tokenize($this->value);
    }

    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    public function setRegex($regex)
    {
        $this->regex = $regex;
        return $this;
    }

    public function setColumnName($columnName)
    {
        $this->columnName = $columnName;
        return $this;
    }

    public function setTokenizer(TokenizerInterface $tokenizer)
    {
        $this->tokenizer = $tokenizer;
    }

    public function getTokenizer()
    {
        if (null === $this->tokenizer) {
            $this->tokenizer = new BasicTokenizer();
        }
        return $this->tokenizer;
    }



}
