<?php

namespace Broda\Core\Provider\Symfony;

use Broda\Core\Container\FormExtensionableProviderInterface;
use Broda\Core\Provider\Symfony\Validator\ConstraintValidatorFactory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\FormFactoryBuilderInterface;
use Symfony\Component\Validator\Mapping\Factory\LazyLoadingMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Validator\Mapping\Loader\LoaderChain;
use Symfony\Component\Validator\Mapping\Loader\StaticMethodLoader;
use Symfony\Component\Validator\Validator;
use Symfony\Component\Validator\DefaultTranslator;

/**
 * Symfony Validator component Provider.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ValidatorProvider implements ServiceProviderInterface, FormExtensionableProviderInterface
{
    public function register(Container $c)
    {
        $c['validator'] = function ($c) {
            if (isset($c['translator']) && method_exists($c['translator'], 'addResource')) {
                $r = new \ReflectionClass('Symfony\Component\Validator\Validator');

                $c['translator']->addResource('xliff', dirname($r->getFilename()) . '/Resources/translations/validators.' . $c['locale'] . '.xlf', $c['locale'], 'validators');
            }

            return new Validator(
                $c['validator.mapping.class_metadata_factory'],
                $c['validator.validator_factory'],
                isset($c['translator']) ? $c['translator'] : new DefaultTranslator(),
                'validators',
                $c['validator.object_initializers']
            );
        };

        $c['validator.mapping.class_metadata_loader'] = function ($c) {
            if (isset($c['annotation.reader'])) {
                return new LoaderChain(array(
                    new AnnotationLoader($c['annotation.reader']),
                    new StaticMethodLoader(),
                ));
            }

            return new StaticMethodLoader();
        };

        $c['validator.mapping.class_metadata_factory'] = function ($c) {
            return new LazyLoadingMetadataFactory($c['validator.mapping.class_metadata_loader']);
        };

        $c['validator.validator_factory'] = function ($c) {
            return new ConstraintValidatorFactory($c);
        };

        // TODO criar o Container\ValidatorExtensionableProviderInterface
        $c['validator.object_initializers'] = function ($c) {
            return array();
        };
    }

    public function formExtensions(Container $c, FormFactoryBuilderInterface $builder)
    {
        $builder
            ->addExtension(new ValidatorExtension($c['validator']))
            ;

        if (isset($c['translator']) && method_exists($c['translator'], 'addResource')) {
            $r = new \ReflectionClass('Symfony\Component\Form\Form');
            $c['translator']->addResource('xliff', dirname($r->getFilename()).'/Resources/translations/validators.'.$c['locale'].'.xlf', $c['locale'], 'validators');
        }
    }


} 