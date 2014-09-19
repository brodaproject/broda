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
     * Processa uma string e transforma num array de strings
     * 
     * @param string $string
     * @return string[]
     */
    function tokenize($string);
}
