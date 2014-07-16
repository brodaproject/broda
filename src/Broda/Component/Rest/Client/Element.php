<?php

namespace Broda\Component\Rest\Client;

/**
 * Classe Element
 *
 * @author raphael
 */
class Element extends AbstractElement
{
    public function toParameters()
    {
        return get_defined_vars();
    }

}
