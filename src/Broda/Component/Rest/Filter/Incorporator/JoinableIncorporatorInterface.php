<?php

namespace Broda\Component\Rest\Filter\Incorporator;

/**
 *
 *
 * @author raphael
 */
interface JoinableIncorporatorInterface extends IncorporatorInterface
{

    public function setFieldMap(array $fieldMap);
} 