<?php

namespace Broda\Core\Controller;

use Broda\Core\Controller\Annotations\Inject;
use Doctrine\Common\Persistence\ConnectionRegistry;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pimple\Container;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Base para todos os controllers do sistema.
 *
 * Você não precisa herdar essa classe se não quiser, porém
 * ela tem vários métodos que te facilitam a vida ao
 * criar controllers.
 *
 * Veja exemplos na própria pasta de controllers (app/controllers).
 */
abstract class Controller
{

    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     *
     * @Inject(Injector::CONTAINER)
     */
    function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Retorna a "tag" do controller, para saber se o usuário
     * logado tem permissão nele por esta tag.
     *
     * Por padrão, é o nome do controller em maiuscula com o prefixo "BRODA_"
     * Ex: ClientesController -> BRODA_CLIENTESCONTROLLER
     *
     * Para entender melhor, veja a classe {@link CMS\Core\Security\User\PermissionMap}.
     *
     * @see CMS\Core\Security\User\PermissionMap
     *
     * @return string
     */
    public function getTag()
    {
        return 'BRODA_'.strtoupper(get_class($this));
    }

    /**
     * Retorna o {@link EntityManager} do Doctrine para lidar com models.
     *
     * Para pegar uma conexão específica, passe através do parametro $conexao.
     *
     * Para pegar o objeto de conexão, é só dar um ->getConnection();
     *
     * @param string $connectionName
     * @return ConnectionRegistry|ManagerRegistry
     */
    public function getDoctrine($connectionName = null)
    {
        if (null === $connectionName) {
            return $this->container['doctrine.registry'];
        }

        $registry = $this->container['doctrine.registry'];
        if ($registry instanceof ManagerRegistry) {
            return $registry->getManager($connectionName);
        }
        return $registry->getConnection($connectionName);
    }

    /**
     * Retorna o usuário que está logado.
     *
     * @return UserInterface
     */
    public function getUser()
    {
        if (null === $token = $this->container['security.context']->getToken()) {
            return null;
        }

        return $token->getUser();
    }

} 