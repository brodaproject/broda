<?php

namespace Broda\Component\Rest\Filter\Incorporator;

/**
 * TODO doc
 *
 * @author raphael
 */
interface JoinableIncorporatorInterface extends IncorporatorInterface
{
    public function setFieldMap(array $fieldMap);
} 