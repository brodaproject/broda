<?php

namespace Broda\Core\Provider\Doctrine\Registry;


use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Pimple\Container;

class OrmRegistry extends DbalRegistry implements ManagerRegistry
{

    /**
     * @var Container
     */
    private $ormContainer;

    /**
     * @var string[]
     */
    private $managerNames = array();

    private $defaultManagerName;

    private $proxyClass;

    /**
     * Construtor.
     *
     * @param Container $dbalContainer
     * @param Container $ormContainer
     * @param string $proxyClass
     * @param array $connectionNames
     * @param array $managerNames
     * @param string $defaultManagerName
     * @param string $defaultConnectionName
     */
    function __construct(Container $dbalContainer, Container $ormContainer, $proxyClass, array $connectionNames, array $managerNames, $defaultManagerName = null, $defaultConnectionName = null)
    {
        parent::__construct($dbalContainer, $connectionNames, $defaultConnectionName);
        $this->ormContainer = $ormContainer;
        $this->proxyClass = $proxyClass;
        $this->managerNames = $managerNames;
        $this->defaultManagerName = !empty($defaultManagerName) ? $defaultManagerName : reset($managerNames);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultManagerName()
    {
        return $this->defaultManagerName;
    }

    /**
     * {@inheritdoc}
     *
     * @return EntityManager
     */
    public function getManager($name = null)
    {
        return $this->ormContainer[$name ?: $this->defaultManagerName];
    }

    /**
     * {@inheritdoc}
     *
     * @return EntityManager[] An array of ObjectManager instances
     */
    public function getManagers()
    {
        $ems = array();
        foreach ($this->managerNames as $name) {
            $ems[] = $this->getManager($name);
        }
        return $ems;
    }

    /**
     * {@inheritdoc}
     *
     * @return EntityManager
     */
    public function resetManager($name = null)
    {
        // TODO: resetar o object manager aqui de alguma forma.
        return $this->getManager($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getAliasNamespace($alias)
    {
        foreach ($this->managerNames as $name) {
            try {
                $config = $this->getManager($name)->getConfiguration();

                if ($config instanceof Configuration) {
                    return $config->getEntityNamespace($alias);
                }

            } catch (\Exception $e) {
            }
        }

        throw new \InvalidArgumentException('Alias namespace not found');
    }

    /**
     * {@inheritdoc}
     */
    public function getManagerNames()
    {
        return $this->managerNames;
    }

    /**
     * {@inheritdoc}
     *
     * @return EntityRepository
     */
    public function getRepository($persistentObject, $persistentManagerName = null)
    {
        return $this->getManager($persistentManagerName)->getRepository($persistentObject);
    }

    /**
     * {@inheritdoc}
     *
     * @return EntityManager|null
     */
    public function getManagerForClass($class)
    {
        // Check for namespace alias
        if (strpos($class, ':') !== false) {
            list($namespaceAlias, $simpleClassName) = explode(':', $class);
            $class = $this->getAliasNamespace($namespaceAlias) . '\\' . $simpleClassName;
        }

        $proxyClass = new \ReflectionClass($class);
        if ($proxyClass->implementsInterface($this->proxyClass)) {
            $class = $proxyClass->getParentClass()->getName();
        }

        foreach ($this->managerNames as $id) {
            $manager = $this->getManager($id);

            if (!$manager->getMetadataFactory()->isTransient($class)) {
                return $manager;
            }
        }

        return null;
    }

} 