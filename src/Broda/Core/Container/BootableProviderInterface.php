<?php

namespace Broda\Core\Container;

use Pimple\Container;

/**
 * Interface que define todos os ServiceProvider que precisam
 * inicializar algumas coisas depois de definir seus serviços
 * e parametros.
 *
 * O método "boot()" é rodado assim que o front controller (index.php)
 * é chamado. Ou seja, qualquer lógica que deve ser executada
 * antes de iniciar o kernel e depois de configurar o container deve
 * estar neste método.
 *
 * Para entender um pouco mais da lógica dele, ver documentação
 * do Silex (a ideia é parecida)
 * http://silex.sensiolabs.org/documentation
 *
 */
interface BootableProviderInterface
{
    public function boot(Container $container);

    /**
     * Deve retornar um número que indica qual é a prioridade em
     * bootar este provider.
     *
     * Quanto maior o número, mais cedo ela é executada.
     *
     * Para prioridade padrão, retorne 0.
     *
     * Pode retornar prioridades negativas também (ex: -20)
     * pra garantir que ela rode sempre DEPOIS de todas.
     *
     * Também dá pra colocar um valor bem alto (ex: 300)
     * para garantir que ela rode sempre ANTES de todas..
     *
     * A ordem geralmente não afeta muita coisa, mas
     * pra alguns providers, ela é importante (ex: {@link CMS\Core\Provider\ErrorHandlerProvider}
     *
     * @return int
     */
    public function getPriority();
} 