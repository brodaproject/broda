<?php

namespace Broda\Component\Rest\Filter\Tokenizers;

/**
 * Classe BasicTokenizer
 *
 * @author raphael
 */
class BasicTokenizer implements TokenizerInterface
{

    public function tokenize($string)
    {
        return explode(' ', $string);
    }

}
