<?php

namespace core\transfer;

/**
 * Classe Transfer
 *
 * @author Sistema13 <sistema13@furacao.com.br>
 */
class ChainTransferAdapter extends AbstractTransferAdapter
{

    /**
     *
     * @var TransferAdapterInterface[]
     */
    protected $adapters = array();

    public function connect()
    {
        $throwEx = null;
        foreach ($this->adapters as $adapter) {
            try {
                var_dump('tentou por '.get_class($adapter));
                return $adapter->connect();
            } catch (TransferException $ex) {
                $throwEx = TransferException::chain($throwEx, $ex);
            }
        }
        // se todos derem erro, lança exception de todos erros
        if (null !== $throwEx) {
            throw $throwEx;
        }
    }

    public function addAdapter(TransferAdapterInterface $adapter)
    {
        $this->adapters[] = $adapter;
    }

    public function getAdapters()
    {
        return $this->adapters;
    }

    public function setUrl($url)
    {
        parent::setUrl($url);
        foreach ($this->adapters as $adapter) {
            $adapter->setUrl($url);
        }
    }

    public function setParameter($key, $value)
    {
        parent::setParameter($key, $value);
        foreach ($this->adapters as $adapter) {
            $adapter->setParameter($key, $value);
        }
    }

    public function addParameters(array $params)
    {
        parent::addParameters($params);
        foreach ($this->adapters as $adapter) {
            $adapter->addParameters($params);
        }
    }

    public function setFunction($functionName)
    {
        parent::setFunction($functionName);
        foreach ($this->adapters as $adapter) {
            $adapter->setFunction($functionName);
        }
    }

}
