<?php

namespace Broda\Core\Container;

use Pimple\Container;
use Symfony\Component\Routing\RouteCollection;

/**
 * Interface para todos os ServiceProviders que necessitam
 * criar rotas novas para o sistema.
 *
 * Elas vão ser criadas após o boot do container (ver {@link BootableProviderInterface}).
 *
 * Lembrando que todos os controller já são mapeados se
 * estiverem na pasta app/controller e que tiverem usando
 * as anotações (@)Route. Use esta interface somente se necessitar
 * criar rotas excepcionais pro sistema.
 *
 */
interface RouteProviderInterface
{
    public function route(Container $container, RouteCollection $routes);
} 