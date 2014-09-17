<?php

namespace Broda\Component\Rest\Filter\Incorporator;


use Broda\Component\Rest\Filter\FilterInterface;

interface IncorporatorInterface
{
    public function incorporate($object, FilterInterface $filter);

    public function setFieldMap(array $fieldMap);
} 