<?php

namespace Broda\Core\Provider\Symfony;


use Broda\Core\Provider\Symfony\Translation\Translator;
use Broda\Core\Provider\Twig\Container\TwigExtensionableProviderInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Bridge\Twig\Extension\TranslationExtension;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Symfony\Component\Translation\Loader\XliffFileLoader;
use Symfony\Component\Translation\MessageSelector;

class TranslatorProvider implements ServiceProviderInterface, TwigExtensionableProviderInterface
{
    public function register(Container $c)
    {
        $c['translator'] = function ($c) {
            if (!isset($c['locale'])) {
                throw new \LogicException('You must register the LocaleServiceProvider to use the TranslationServiceProvider');
            }
            $translator = new Translator($c, $c['translator.message_selector']);
            $translator->setFallbackLocales($c['locale_fallbacks']);
            $translator->addLoader('array', new ArrayLoader());
            $translator->addLoader('xliff', new XliffFileLoader());

            foreach ($c['translator.domains'] as $domain => $data) {
                foreach ($data as $locale => $messages) {
                    $translator->addResource('array', $messages, $locale, $domain);
                }
            }

            return $translator;
        };

        $c['translator.message_selector'] = function () {
            return new MessageSelector();
        };

        $c['translator.domains'] = array();
        $c['locale_fallbacks'] = array('en');
    }

    public function twigExtensions(Container $c, \Twig_Environment $twig)
    {
        $twig->addExtension(new TranslationExtension($c['translator']));
    }

} 