<?php

namespace Broda\Component\Rest\Filter\Tokenizers;

/**
 * Interface TokenizerInterface
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
