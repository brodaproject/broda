<?php

namespace Broda\Component\Routing\Loader;

use Symfony\Component\Routing\Loader\AnnotationClassLoader as BaseAnnotationClassLoader;

/**
 * Description of AnnotationClassLoader
 *
 * @author raphael
 */
class AnnotationClassLoader extends BaseAnnotationClassLoader
{

    protected function configureRoute(\Symfony\Component\Routing\Route $route,
            \ReflectionClass $class, \ReflectionMethod $method, $annot)
    {
        // defines the controller
        $route->setDefault('_controller', $class->getName().'::'.$method->getName());
        // verify the other callbacks
        $options = $annot->getOptions();
        foreach ($options as $prop => &$values) {

            if (!in_array($prop, array('_after_middlewares', '_before_middlewares', '_converters')))
                continue;

            if (empty($values)) continue;

            foreach ($values as &$value) {
                if (is_string($value) && $class->hasMethod($value)) {
                    // call static method from class
                    $value = array($class->getName(), $value);
                }
            }
            unset($value); // clear reference

        }
        unset($values);

        $route->setOptions($options);
    }

}
