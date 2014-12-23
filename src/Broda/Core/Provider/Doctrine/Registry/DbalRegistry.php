<?php

namespace Broda\Core\Provider\Doctrine\Registry;


use Doctrine\Common\Persistence\ConnectionRegistry;
use Doctrine\DBAL\Connection;
use Pimple\Container;

class DbalRegistry implements ConnectionRegistry
{

    /**
     * @var Container
     */
    private $dbalContainer;

    /**
     * @var string[]
     */
    private $connectionNames = array();

    private $defaultName;

    /**
     * Construtor.
     *
     * @param Container $dbalContainer
     * @param array $connectionNames
     * @param string $defaultConnectionName
     */
    function __construct(Container $dbalContainer, array $connectionNames, $defaultConnectionName = null)
    {
        $this->dbalContainer = $dbalContainer;
        $this->connectionNames = $connectionNames;
        $this->defaultName = !empty($defaultConnectionName) ? $defaultConnectionName : reset($connectionNames);
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultConnectionName()
    {
        return $this->defaultName;
    }

    /**
     * {@inheritdoc}
     *
     * @return Connection
     */
    public function getConnection($name = null)
    {
        return $this->dbalContainer[$name ?: $this->defaultName];
    }

    /**
     * {@inheritdoc}
     *
     * @return Connection[]
     */
    public function getConnections()
    {
        $conns = array();
        foreach ($this->connectionNames as $name) {
            $conns[] = $this->getConnection($name);
        }
        return $conns;
    }

    /**
     * {@inheritdoc}
     */
    public function getConnectionNames()
    {
        return $this->connectionNames;
    }

} 