<?php
/*
 * This file bootstraps the test environment.
 */
namespace Broda\Tests;

error_reporting(E_ALL & ~E_NOTICE | E_STRICT);

// register silently failing autoloader
spl_autoload_register(function($class) {
    if (0 === strpos($class, 'Broda\Tests\\')) {
        $path = __DIR__.'/../../'.strtr($class, '\\', '/').'.php';
        if (is_file($path) && is_readable($path)) {
            require_once $path;

            return true;
        }
    }
});

$loader = require_once __DIR__ . "/../../../vendor/autoload.php";

\Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
