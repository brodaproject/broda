<?php

namespace Broda\Tests\Component\Rest\Fixtures;

use JMS\Serializer\Annotation as JMS;

class SerializableObject
{
    /**
     * @JMS\Type("string")
     */
    public $prop = 'value';
}
