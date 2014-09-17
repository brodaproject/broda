<?php

namespace Broda\Framework\Provider\Doctrine;

use Doctrine\Common\Persistence\AbstractManagerRegistry;
use Doctrine\Common\Persistence\PersistentObject;
use Doctrine\ORM\Configuration;
use Pimple\Container;

/**
 * Classe Registry
 *
 * Esta classe é como a 'dbs' ou 'orm.ems': ela guarda todas as conexões e managers
 * do Doctrine num registro.
 *
 * Serve principalmente para integraçao com algum bundle do Symfony, já que
 * no Symfony o service 'doctrine' é um Registry
 * Para bundles que precisam de Registry (no caso, o JMS\Serializer)
 *
 */
class Registry extends AbstractManagerRegistry
{

    /**
     *
     * @var Container
     */
    private $sc;

    public function __construct(
            Container $sc,
            array $connections,
            array $managers,
            $defaultConnection,
            $defaultManager
    ) {
        $this->sc = $sc;
        parent::__construct(
                'doctrine',
                $connections,
                $managers,
                $defaultConnection,
                $defaultManager,
                'Doctrine\Common\Proxy\Proxy'
        );
    }

    protected function getService($name)
    {
        list($type, $name) = explode(':', $name, 2);
        return $this->sc[$type][$name];
    }

    protected function resetService($name)
    {
        list($type, $name) = explode(':', $name, 2);
        unset($this->sc[$type][$name]);
    }

    public function getManagerForClass($class)
    {
        if (is_subclass_of($class, 'Doctrine\Common\Persistence\PersistentObject')) {
            return PersistentObject::getObjectManager();
        }
        return parent::getManagerForClass($class);
    }

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

}
