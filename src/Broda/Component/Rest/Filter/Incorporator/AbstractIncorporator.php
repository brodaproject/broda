<?php

namespace Broda\Component\Rest\Filter\Incorporator;


use Broda\Component\Rest\RestService;

abstract class AbstractIncorporator implements IncorporatorInterface
{
    /**
     * @var RestService
     */
    protected $rest;

    protected $fieldMap = array();

    function __construct(RestService $rest)
    {
        $this->rest = $rest;
    }

    public function setFieldMap(array $fieldMap)
    {
        $this->fieldMap = $fieldMap;
    }

} 