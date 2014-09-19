<?php

namespace Broda\Component\Rest\Filter;


/**
 * Filtro criado pelo {@link FilterBuilder}.
 *
 * @author raphael
 */
class GenericFilter extends AbstractFilter
{
    protected $outputCallback;

    public function setOutputCallback($callback)
    {
        $this->outputCallback = $callback;
    }

    /**
     * {@inheritdoc}
     */
    public function getOutputResponse($output)
    {
        if (null !== $this->outputCallback) {
            return call_user_func($this->outputCallback, $output, $this);
        }
        return parent::getOutputResponse($output);
    }
} 