<?php

namespace Broda\Core\Provider\Doctrine;


use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\Configuration;
use Pimple\Container;

class OrmRegistry extends DbalRegistry implements ManagerRegistry
{

    /**
     * @var string[]
     */
    protected $managerNames = array();

    protected $defaultManagerName;

    function __construct(Container $container, array $connectionNames, array $managerNames, $defaultManagerName = null, $defaultConnectionName = null)
    {
        parent::__construct($container, $connectionNames, $defaultConnectionName);
        $this->managerNames = $managerNames;
        $this->defaultManagerName = !empty($defaultManagerName) ? $defaultManagerName : reset($managerNames);
    }

    /**
     * Gets the default object manager name.
     *
     * @return string The default object manager name.
     */
    public function getDefaultManagerName()
    {
        return $this->defaultManagerName;
    }

    /**
     * Gets a named object manager.
     *
     * @param string $name The object manager name (null for the default one).
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    public function getManager($name = null)
    {
        return $this->container['orm.ems'][$name ?: $this->defaultManagerName];
    }

    /**
     * Gets an array of all registered object managers.
     *
     * @return \Doctrine\Common\Persistence\ObjectManager[] An array of ObjectManager instances
     */
    public function getManagers()
    {
        $ems = array();
        foreach ($this->getManagerNames() as $name) {
            $ems[] = $this->getManager($name);
        }
        return $ems;
    }

    /**
     * Resets a named object manager.
     *
     * This method is useful when an object manager has been closed
     * because of a rollbacked transaction AND when you think that
     * it makes sense to get a new one to replace the closed one.
     *
     * Be warned that you will get a brand new object manager as
     * the existing one is not useable anymore. This means that any
     * other object with a dependency on this object manager will
     * hold an obsolete reference. You can inject the registry instead
     * to avoid this problem.
     *
     * @param string|null $name The object manager name (null for the default one).
     *
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    public function resetManager($name = null)
    {
        // TODO: resetar o object manager aqui de alguma forma.
        return $this->getManager($name);
    }

    /**
     * Resolves a registered namespace alias to the full namespace.
     *
     * This method looks for the alias in all registered object managers.
     *
     * @param string $alias The alias.
     *
     * @return string The full namespace.
     */
    public function getAliasNamespace($alias)
    {
        foreach ($this->getManagerNames() as $name) {
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
     * Gets all connection names.
     *
     * @return array An array of connection names.
     */
    public function getManagerNames()
    {
        return $this->managerNames;
    }

    /**
     * Gets the ObjectRepository for an persistent object.
     *
     * @param string $persistentObject The name of the persistent object.
     * @param string $persistentManagerName The object manager name (null for the default one).
     *
     * @return \Doctrine\Common\Persistence\ObjectRepository
     */
    public function getRepository($persistentObject, $persistentManagerName = null)
    {
        return $this->getManager($persistentManagerName)->getRepository($persistentObject);
    }

    /**
     * Gets the object manager associated with a given class.
     *
     * @param string $class A persistent object class name.
     *
     * @return \Doctrine\Common\Persistence\ObjectManager|null
     */
    public function getManagerForClass($class)
    {
        // Check for namespace alias
        if (strpos($class, ':') !== false) {
            list($namespaceAlias, $simpleClassName) = explode(':', $class);
            $class = $this->getAliasNamespace($namespaceAlias) . '\\' . $simpleClassName;
        }

        $proxyClass = new \ReflectionClass($class);
        if ($proxyClass->implementsInterface('Doctrine\Common\Persistence\Proxy')) {
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