<?php

namespace Broda\Core\Provider\Symfony;


use Broda\Core\Container\FormExtensionableProviderInterface;
use Broda\Core\Provider\Twig\Container\TwigExtensionableProviderInterface;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Symfony\Bridge\Twig\Extension\FormExtension;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Bridge\Twig\Form\TwigRendererEngine;
use Symfony\Component\Form\Extension\Csrf\CsrfExtension;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\DefaultCsrfProvider;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\SessionCsrfProvider;
use Symfony\Component\Form\Extension\HttpFoundation\HttpFoundationExtension;
use Symfony\Component\Form\FormFactoryBuilderInterface;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\ResolvedFormTypeFactory;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\TokenGenerator\UriSafeTokenGenerator;
use Symfony\Component\Security\Csrf\TokenStorage\SessionTokenStorage;

class FormProvider implements ServiceProviderInterface, FormExtensionableProviderInterface, TwigExtensionableProviderInterface
{
    public function register(Container $c)
    {
        if (!class_exists('Locale') && !class_exists('Symfony\Component\Locale\Stub\StubLocale')) {
            throw new \RuntimeException('You must either install the PHP intl extension or the Symfony Locale Component to use the Form extension.');
        }

        if (!class_exists('Locale')) {
            $r = new \ReflectionClass('Symfony\Component\Locale\Stub\StubLocale');
            $path = dirname(dirname($r->getFilename())).'/Resources/stubs';

            require_once $path.'/functions.php';
            require_once $path.'/Collator.php';
            require_once $path.'/IntlDateFormatter.php';
            require_once $path.'/Locale.php';
            require_once $path.'/NumberFormatter.php';
        }

        $c['form.secret'] = md5(__DIR__);

        $c['form.factory_builder'] = function ($c) {
            return Forms::createFormFactoryBuilder();
        };

        $c['form.factory'] = function ($c) {
            return $c['form.factory_builder']->getFormFactory();
        };

        $c['form.resolved_type_factory'] = function ($c) {
            return new ResolvedFormTypeFactory();
        };

        $c['form.extension.csrf'] = function ($c) {
            return new CsrfExtension($c['form.csrf_provider'], isset($c['translator']) ? $c['translator'] : null, $c['locale']);
        };

        $c['form.csrf_provider'] = function ($c) {
            if (isset($c['security.context'])) {
                return new CsrfTokenManager(
                    new UriSafeTokenGenerator(),
                    new SessionTokenStorage($c['session'])
                );
            }

            if (isset($c['session'])) {
                return new SessionCsrfProvider($c['session'], $c['form.secret']);
            }

            return new DefaultCsrfProvider($c['form.secret']);
        };
    }

    public function formExtensions(Container $c, FormFactoryBuilderInterface $builder)
    {
        $builder
            ->addExtensions(array(
                $c['form.extension.csrf'],
                new HttpFoundationExtension(),
            ))
            ->setResolvedTypeFactory($c['form.resolved_type_factory'])
        ;
    }

    public function twigExtensions(Container $c, \Twig_Environment $twig)
    {
        $c['twig.form.templates'] = array('form_div_layout.html.twig');

        $c['twig.form.engine'] = function ($c) {
            return new TwigRendererEngine($c['twig.form.templates']);
        };

        $c['twig.form.renderer'] = function ($c) {
            return new TwigRenderer($c['twig.form.engine'], $c['form.csrf_provider']);
        };

        $twig->addExtension(new FormExtension($c['twig.form.renderer']));

        // add loader for Symfony built-in form templates
        $reflected = new \ReflectionClass('Symfony\Bridge\Twig\Extension\FormExtension');
        $path = dirname($reflected->getFileName()).'/../Resources/views/Form';

        $c['twig.loader']->addLoader(new \Twig_Loader_Filesystem($path));
    }


} 