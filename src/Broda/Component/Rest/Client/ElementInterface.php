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
     * Retorna o resource pelo qual o elemento está representado
     *
     * @return ResourceInterface|null
     */
    public function getResource();

    /**
     * @api
     *
     * Define o resource do elemento
     *
     * @param ResourceInterface
     * @throws ResourceException Se tentar mudar o resource que já foi definido
     */
    public function setResource(ResourceInterface $resource);

    /**
     * @return array Todas as propriedades do objeto em array
     */
    public function toParameters();

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
