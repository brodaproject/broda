<?php

namespace Broda\Component\Rest\Filter\Tokenizers;

/**
 * Cria um único token com toda string.
 *
 * É útil para buscas onde cada caractere interessa pra pesquisa.
 *
 * @author raphael
 */
class NoTokenizer implements TokenizerInterface
{
    /**
     * {@inheritdoc}
     */
    function tokenize($string)
    {
        if (empty($string)) {
            return array();
        }
        return array((string)$string);
    }

} 