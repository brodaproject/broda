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

    }

}
