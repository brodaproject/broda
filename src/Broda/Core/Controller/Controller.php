<?php

namespace Broda\Core\Controller;

use Broda\Core\Controller\Annotations\Inject;
use Doctrine\Common\Persistence\ConnectionRegistry;
use Doctrine\Common\Persistence\ManagerRegistry;
use Pimple\Container;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Acl\Voter\FieldVote;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
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
     * @Inject(Broda\Core\Controller\Injector\AnnotationInjector::CONTAINER)
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Retorna o {@link EntityManager} do Doctrine para lidar com models.
     *
     * Para pegar uma conexão específica, passe através do parametro $conexao.
     *
     * Para pegar o objeto de conexão, é só dar um ->getConnection();
     *
     * @param string $connectionName
     * @return ConnectionRegistry|ManagerRegistry|null
     */
    public function getDoctrine($connectionName = null)
    {
        if (!isset($this->container['doctrine.registry'])) {
            return null;
        }

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
     * @return UserInterface|null
     */
    public function getUser()
    {
        if (!isset($this->container['security.context'])) {
            return null;
        }

        /* @var $token TokenInterface */
        if (null === $token = $this->container['security.context']->getToken()) {
            return null;
        }

        return $token->getUser();
    }

    /**
     * @return Session|null
     */
    public function getSession()
    {
        return isset($this->container['session'])
            ? $this->container['session']
            : null;
    }

    /**
     * @return \Twig_Environment
     */
    public function getTwig()
    {
        return isset($this->container['twig'])
            ? $this->container['twig']
            : null;
    }

    /**
     * @return MutableAclProviderInterface|null
     */
    public function getAcl()
    {
        return isset($this->container['security.acl_provider'])
            ? $this->container['security.acl_provider']
            : null;
    }

    /**
     * @return FormFactoryInterface|null
     */
    public function getFormFactory()
    {
        return isset($this->container['form.factory'])
            ? $this->container['form.factory']
            : null;
    }

    /**
     * @param string $type
     * @param null $data
     * @param array $options
     * @return null|\Symfony\Component\Form\FormBuilderInterface
     */
    public function getFormBuilder($type = 'form', $data = null, array $options = array())
    {
        if (null === $this->getFormFactory()) {
            return null;
        }
        return $this->getFormFactory()->createBuilder($type, $data, $options);
    }

    /**
     * @param string|string[] $attributes
     * @param mixed $object
     * @param string $field
     * @return bool
     */
    public function isGranted($attributes, $object = null, $field = null)
    {
        if (!isset($this->container['security.context'])) {
            return false;
        }

        /* @var $checker AuthorizationCheckerInterface */
        $checker = $this->container['security.context'];

        if (null !== $field) {
            $object = new FieldVote($object, $field);
        }

        return $checker->isGranted($attributes, $object);
    }

} 