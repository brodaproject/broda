<?php

namespace Broda\Component\Routing\Annotation;

use Symfony\Component\Routing\Annotation\Route as BaseRoute;

/**
 * Extension for annotation class @Route().
 *
 * @Annotation
 * @Target({"CLASS", "METHOD"})
 *
 * @author raphael
 */
class Route extends BaseRoute
{

    public function setOptions($options)
    {
        $oldOptions = parent::getOptions();
        parent::setOptions(array_merge($oldOptions, $options));
    }

    public function setAsserts($requirements)
    {
        $this->setRequirements($requirements);
    }

    public function getAsserts()
    {
        return $this->getRequirements();
    }

    public function setConverts($callbacks)
    {
        $this->setOptions(array('_converters' => $callbacks));
    }

    public function getConverts()
    {
        $options = $this->getOptions();
        return $options['_converters'];
    }

    public function setBefore($befores)
    {
        $this->setOptions(array('_before_middlewares' => (array)$befores));
    }

    public function getBefore()
    {
        $options = $this->getOptions();
        return $options['_before_middlewares'];
    }

    public function setAfter($after)
    {
        $this->setOptions(array('_after_middlewares' => (array)$after));
    }

    public function getAfter()
    {
        $options = $this->getOptions();
        return $options['_after_middlewares'];
    }

    public function setMethods($methods)
    {
        if (is_string($methods)) {
            $methods = explode('|', $methods);
        }
        parent::setMethods($methods);
    }

}
