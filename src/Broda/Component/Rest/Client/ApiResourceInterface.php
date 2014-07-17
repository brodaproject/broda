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

    /**
     * Define o path do resource.
     *
     * O path suporta o seguinte formato:
     *
     * /resource/{id}.format?query1=value1&q=v
     *
     * A query string é opcional e pode ser definida pelo setParameters().
     * O {id} é obrigatório e indica o placeholder de onde o identifier
     * do resource irá ficar.
     * O format é opcional e é considerado tudo que estiver depois do ponto.
     *
     * @param type $path
     * @return ApiResourceInterface
     */
    public function setPath($path);

    /**
     * Retorna o path do resource, o mesmo que foi definido.
     *
     * @return string
     */
    public function getPath();

    /**
     * Retorna os parametros query string que serão passados em todos os requests
     * deste resource.
     *
     * @return array
     */
    public function getParameters();

    /**
     * Retorna um parametro da query string que será passada em todos os requests
     * deste resource.
     *
     * @return string
     */
    public function getParameter($key);

    /**
     * Define os parametros query string que serão passados em todos os requests
     * deste resource.
     *
     * Substitui qualquer parametro já definido.
     *
     * @param array $params
     * @return ApiResourceInterface
     */
    public function setParameters(array $params);

    /**
     * Define um parametro da query string que será passada em todos os requests
     * deste resource.
     *
     * Substitui o valor do key que já existir.
     *
     * @param string $key
     * @param string $value
     * @return ApiResourceInterface
     */
    public function setParameter($key, $value);

    /**
     * Retorna o nome do resource definido no Api
     *
     * @return string
     */
    public function getName();

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
