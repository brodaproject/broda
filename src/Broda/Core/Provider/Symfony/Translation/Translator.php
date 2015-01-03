<?php

namespace Broda\Core\Provider\Symfony\Translation;

use Pimple\Container;
use Symfony\Component\Translation\Translator as BaseTranslator;
use Symfony\Component\Translation\MessageSelector;

/**
 * Translator that gets the current locale from the container.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Translator extends BaseTranslator
{
    protected $app;

    public function __construct(Container $app, MessageSelector $selector)
    {
        $this->app = $app;
        // TODO implementar o cache_dir e o debug
        parent::__construct(null, $selector);
    }

    public function getLocale()
    {
        return $this->app['locale'];
    }

    public function setLocale($locale)
    {
        if (null === $locale) {
            return;
        }

        $this->app['locale'] = $locale;

        parent::setLocale($locale);
    }
}