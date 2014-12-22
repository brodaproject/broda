<?php

namespace Broda\Core\Provider\Doctrine;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Cache\ArrayCache;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class AnnotationsProvider implements ServiceProviderInterface
{

    function __construct($autoload)
    {
        if (is_object($autoload) && method_exists($autoload, 'loadClass')) {
            // support for composer/symfony's autoloader
            AnnotationRegistry::registerLoader(array($autoload, 'loadClass'));
        } elseif (is_callable($autoload)) {
            AnnotationRegistry::registerLoader($autoload);
        } elseif (is_array($autoload) && is_string($autoload[0])) {
            AnnotationRegistry::registerAutoloadNamespace($autoload[0], $autoload[1]);
        } elseif (is_array($autoload) && is_string(key($autoload))) {
            AnnotationRegistry::registerAutoloadNamespace(key($autoload), reset($autoload));
        } elseif (is_string($autoload)) {
            AnnotationRegistry::registerFile($autoload);
        } else {
            throw new \LogicException('Not a valid Annotation loader. Must be a Composer Autoloader instance, a callable, a array with namespace/path format or a string with path to a register file');
        }
    }

    /**
     * Registers services on the given container.
     *
     * This method should only be used to configure services and parameters.
     * It should not get services.
     *
     * @param Container $pimple An Container instance
     */
    public function register(Container $pimple)
    {
        $c['annotation.reader'] = function ($c) {
            return new CachedReader(new AnnotationReader(), new ArrayCache());
        };
    }

} 