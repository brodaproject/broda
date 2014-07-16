<?php

namespace Broda\Component\Rest\Client;

use Doctrine\Common\Collections\Criteria;

/**
 * Interface ResourceInterface
 *
 * @author raphael
 */
interface ResourceInterface
{
    /**
     * Retorna um elemento pelo id
     *
     * @param type $id
     * @return object
     */
    public function find($id);

    /**
     * Retorna todos os elementos, filtrados ou não por um criterio
     *
     * @param array|Criteria $criteria
     * @return object[]
     */
    public function all($criteria = null);

    /**
     * Salva ou adiciona (dependendo do contexto) um elemento
     *
     * @param object $element
     * @return bool Sempre TRUE
     * @throws ResourceException Se houver algum erro
     */
    public function save($element);

    /**
     * Deleta um elemento
     *
     * @param object $element
     * @return bool Sempre TRUE
     * @throws ResourceException Se houver algum erro
     */
    public function delete($element);

    /**
     * Aciona um trigger do elemento
     *
     * @param object $element
     * @param type $action Nome da ação do trigger
     * @return bool Sempre TRUE
     * @throws ResourceException Se houver algum erro
     */
    public function trigger($element, $action);

}
