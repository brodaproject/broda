<?php

namespace Broda\Core\Container;


use Pimple\Container;
use Symfony\Component\Form\FormFactoryBuilderInterface;

interface FormExtensionableProviderInterface
{
    public function formExtensions(Container $c, FormFactoryBuilderInterface $builder);
} 