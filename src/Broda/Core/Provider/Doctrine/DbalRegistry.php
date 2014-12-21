<?php

namespace Broda\Core\Provider\Doctrine;


use Doctrine\Common\Persistence\ConnectionRegistry;
use Pimple\Container;

class DbalRegistry implements ConnectionRegistry
{

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var string[]
     */
    protected $connectionNames = array();

    protected $defaultName;

    function __construct(Container $container, array $connectionNames, $defaultConnectionName = null)
    {
        $this->container = $container;
        $this->connectionNames = $connectionNames;
        $this->defaultName = !empty($defaultConnectionName) ? $defaultConnectionName : reset($connectionNames);
    }

    /**
     * Gets the default connection name.
     *
     * @return string The default connection name.
     */
    public function getDefaultConnectionName()
    {
        return $this->defaultName;
    }

    /**
     * Gets the named connection.
     *
     * @param string $name The connection name (null for the default one).
     *
     * @return object
     */
    public function getConnection($name = null)
    {
        return $this->container['dbal.conns'][$name ?: $this->defaultName];
    }

    /**
     * Gets an array of all registered connections.
     *
     * @return array An array of Connection instances.
     */
    public function getConnections()
    {
        $conns = array();
        foreach ($this->getConnectionNames() as $name) {
            $conns[] = $this->getConnection($name);
        }
        return $conns;
    }

    /**
     * Gets all connection names.
     *
     * @return array An array of connection names.
     */
    public function getConnectionNames()
    {
        return $this->connectionNames;
    }

} 