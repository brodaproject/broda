<?php

namespace Broda\Component\Rest\Client;

/**
 * Classe ElementMetadata
 *
 * @author raphael
 */
class ElementMetadata
{
    const ACCESS_DIRECT = 1;
    const ACCESS_PUBLIC_METHOD = 2;
    
    public $identifier;

    public $access = self::ACCESS_DIRECT;
}
