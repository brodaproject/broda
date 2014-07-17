<?php

namespace Broda\Component\Rest\Client;

/**
 * Interface ElementInterface
 *
 * @author raphael
 */
interface ElementInterface
{

    /**
     * @return mixed
     */
    public function getId();

    /**
     *
     * @param mixed $id
     * @return ElementInterface
     */
    public function setId($id);

    /**
     * Salva o elemento
     *
     * @return bool Sempre TRUE
     * @throws ResourceException Se houver algum erro
     */
    public function save();

    /**
     * Deleta o elemento
     *
     * @return bool Sempre TRUE
     * @throws ResourceException Se houver algum erro
     */
    public function delete();

    /**
     * Aciona um trigger do elemento
     *
     * @param type $action Nome da ação do trigger
     * @return bool Sempre TRUE
     * @throws ResourceException Se houver algum erro
     */
    public function trigger($action);
}
