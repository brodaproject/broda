<?php

namespace Broda\Core\Provider\Twig\Container;


use Pimple\Container;

interface TwigExtensionableProviderInterface
{
    public function twigExtensions(Container $c, \Twig_Environment $twig);
} 