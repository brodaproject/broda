<?php

namespace Broda\Component\Rest\Client;

/**
 * Interface ApiResourceInterface
 *
 * Todas as criadoras de ApiResources devem implementar esta interface
 *
 * Com elas é possível criar uma resource
 *
 * Ex:
 * $api = new Api('http://api.ex.com/v1');
 * $r = $api->createResource('person', '/people/{id}', 'Project\Element\Person', 'json'); // http://api.ex.com/v1/people/123
 *
 * @author raphael
 */
interface ApiResourceCreatorInterface
{
    /**
     * Cria um ApiResource
     *
     * @param string $name Nome do resource. Subresources usam notação por ponto (ex: 'person.article')
     * @param string $path
     * @param string $class
     * @param string $format
     * @return ApiResourceInterface
     */
    public function createResource($name, $path, $class = 'stdClass', $format = 'json');

    /**
     * Retorna um ApiResource já criado
     *
     * @param string $name Nome do resource. Subresources usam notação por ponto (ex: 'person.article')
     * @return ApiResourceInterface
     */
    public function getResource($name);
}
