<?php

namespace Broda\Component\Rest\Client;

/**
 * Interface ApiResourceInterface
 *
 * Todas as ApiResource devem implementar esta interface
 * Ela define toda ApiResource, e por ela extender da criadora, com ela é possível
 * criar 'sub-resources' também.
 *
 * Ex:
 * $api = new Api('http://api.ex.com/v1');
 * $r = $api->createResource('person', 'Project\Element\Person', '/people/{id}', 'json'); // http://api.ex.com/v1/people/123
 * $sr = $r->createResource('article', 'Project\Element\Person\Article', '/articles/{id}', 'json');  // http://api.ex.com/v1/people/123/articles/456
 *
 *
 * @author raphael
 *
 * @todo repensar nessa ideia de subresources... não está prático para chamadas de métodos
 * delas, pois não tenho referencia de qual pai ela tem para o que ela deve trazer
 */
interface ApiResourceInterface /*extends ApiResourceCreatorInterface*/
{

    /**
     * Define a api do apiresource
     *
     * @param Api $api
     * @return ApiResourceInterface
     */
    public function setApi(Api $api);

    /**
     * Retorna a api do apiresource
     *
     * @return Api
     */
    public function getApi();

    /**
     * Define a classe das instâncias que serão retornadas pela api após serem
     * mapeadas pelos dados de retorno dos requests.
     *
     * @param string $class
     * @return ApiResourceInterface
     */
    public function setClass($class = 'stdClass');

    /**
     * Retorna a classe das instâncias que serão retornadas pela api após serem
     * mapeadas pelos dados de retorno dos requests.
     *
     * @return string
     */
    public function getClass();

    /**
     * Define o formato dos requests.
     *
     * Padrão: json
     * Suportados: xml, json
     *
     * @param string $format
     * @return ApiResourceInterface
     */
    public function setFormat($format = 'json');

    /**
     * Retorna o formato dos requests.
     *
     * @return string
     */
    public function getFormat();

    // TODO doc
    public function setPath($path);

    // TODO doc
    public function getPath();

    // TODO doc
    public function getParameters();

    // TODO doc
    public function getParameter($key);

    // TODO doc
    public function setParameters(array $params);

    // TODO doc
    public function setParameter($key, $value);

    /**
     * Retorna o caminho completo formatado do resource
     *
     * ex:
     * $r->getCurrentPath()    // http://api.ex.com/v1/person.json
     * $r->getCurrentPath(123) // http://api.ex.com/v1/person/123.json
     *
     * @param mixed $id
     * @param string $action
     * @param array $append
     * @return string
     */
    public function getCurrentPath($id = null, $action = null, array $append = array());

}
