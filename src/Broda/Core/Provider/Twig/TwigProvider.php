<?php

namespace Broda\Core\Provider\Twig;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Twig integration for Silex.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class TwigProvider implements ServiceProviderInterface
{
    public function register(Container $c)
    {
        $c['twig.options'] = array();
        $c['twig.path'] = array();
        $c['twig.templates'] = array();

        $c['twig'] = function ($c) {
            $c['twig.options'] = array_replace(
                array(
                    'charset'          => $c['charset'],
                    'debug'            => $c['debug'],
                    'strict_variables' => $c['debug'],
                ), $c['twig.options']
            );

            $twig = new \Twig_Environment($c['twig.loader'], $c['twig.options']);
            $twig->addGlobal('app', $c);

            if ($c['debug']) {
                $twig->addExtension(new \Twig_Extension_Debug());
            }

            return $twig;
        };

        $c['twig.loader.filesystem'] = function ($c) {
            return new \Twig_Loader_Filesystem($c['twig.path']);
        };

        $c['twig.loader.array'] = function ($c) {
            return new \Twig_Loader_Array($c['twig.templates']);
        };

        $c['twig.loader'] = function ($c) {
            return new \Twig_Loader_Chain(array(
                $c['twig.loader.array'],
                $c['twig.loader.filesystem'],
            ));
        };
    }
} 