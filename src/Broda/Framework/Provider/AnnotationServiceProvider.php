<?php

namespace Broda\Framework\Provider;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Classe DoctrineAnnotationServiceProvider
 *
 */
class AnnotationServiceProvider implements ServiceProviderInterface
{

    public function __construct(array $loaders = array(), array $globalIgnores = array())
    {
        foreach ($loaders as $loader) {
            if (is_object($loader) && method_exists($loader, 'loadClass')) {
                // support for composer/symfony's autoloader
                AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
            } elseif (is_callable($loader)) {
                AnnotationRegistry::registerLoader($loader);
            } elseif (is_array($loader) && is_string($loader[0])) {
                AnnotationRegistry::registerAutoloadNamespace($loader[0], $loader[1]);
            } elseif (is_array($loader) && is_string(key($loader))) {
                AnnotationRegistry::registerAutoloadNamespace(key($loader), reset($loader));
            } elseif (is_string($loader)) {
                AnnotationRegistry::registerFile($loader);
            } else {
                throw new \LogicException('Not a valid Annotation loader. Must be a Composer Autoloader instance, a callable, a array with namespace/path format or a string with path to a register file');
            }
        }

        foreach ($globalIgnores as $annotation) {
            AnnotationReader::addGlobalIgnoredName($annotation);
        }
    }

    public function register(Container $sc)
    {
        $sc['annotation.cache'] = function () {
            return new ArrayCache();
        };

        $sc['annotation.reader'] = function () {
            return new AnnotationReader();
        };
    }

}
