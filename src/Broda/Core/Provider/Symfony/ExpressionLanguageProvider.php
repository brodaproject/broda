<?php

namespace Broda\Core\Provider\Symfony;


use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class ExpressionLanguageProvider implements ServiceProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Container $c)
    {
        $c['expression_language'] = function ($c) {
            return new ExpressionLanguage();
        };
    }

} 