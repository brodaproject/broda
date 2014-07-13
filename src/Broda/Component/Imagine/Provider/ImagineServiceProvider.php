<?php

namespace Broda\Component\Imagine\Provider;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

/**
 * Classe ImagineServiceProvider
 *
 */
class ImagineServiceProvider implements ServiceProviderInterface
{

    public function register(Container $app)
    {
        $app['imagine.factory'] = function () {
            if (extension_loaded('imagick') && class_exists('\Imagick')) {
                return 'Imagick';
            }
            elseif (class_exists('\Gmagick')) {
                return 'Gmagick';
            }
            return 'Gd';
        };

        $app['imagine'] = function ($app) {
            $class = sprintf('\Imagine\%s\Imagine', $app['imagine.factory']);
            return new $class();
        };
    }

}
