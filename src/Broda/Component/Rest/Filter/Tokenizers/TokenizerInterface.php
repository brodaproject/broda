<?php

namespace Broda\Component\Rest\Filter\Tokenizers;

/**
 * TODO: doc
 *
 * @author raphael
 */
interface TokenizerInterface
{
    /**
     * Processa uma string e transforma num array de strings.
     * 
     * @param string $string String a ser "tokenizada"
     * @return string[] Array com os tokens
     */
    function tokenize($string);
}
