<?php

namespace Broda\Component\Rest\Client;

use Broda\Component\Rest\RestService;

/**
 * Classe ResourceManager
 *
 * Controla os resources de maneira mais eficiente.
 * Você precisa ter definido uma Api e criado os resources dela antes.
 *
 * A diferença de você usar o manager é que você pode centralizar
 * tudo num lugar só (como num arquvo de configuração, por ex), e usar o
 * manager para apenas chamar estes resources, em vez de criar toda vez que
 * precisar. O manager sempre retorna uma instancia nova de resource.
 *
 * Ex:
 * // definindo uma api e seus resources
 * $api = new Api('http://api.urlqualquer/');
 * $api->createResource('clientes', 'clientes/{id}', 'Project\Model\Cliente', 'json');
 * $api->createResource('faturas', 'faturas/{id}', 'Project\Model\Fatura', 'json');
 *
 * // em vez de
 * $restService = new RestService(new JMS\Serializer());
 * $clientesRes = new Resource($restService, $api->getResource('clientes'));
 * $faturasRes = new Resource($restService, $api->getResource('faturas'));
 *
 * // fazer
 * $restService = new RestService(new JMS\Serializer());
 * $manager = new ResourceManager($restService);
 * $clientesRes = $manager->getResource($api, 'clientes');
 * $faturasRes = $manager->getResource($api, 'faturas');
 *
 * // ou ainda, com uma api default
 * $manager = new ResourceManager($restService, $api);
 * $clientesRes = $manager->get('clientes');
 * $faturasRes = $manager->get('faturas');
 *
 * @author raphael
 */
class ResourceManager
{
    /**
     *
     * @var RestService
     */
    protected $rest;

    /**
     *
     * @var Api
     */
    protected $defaultApi;

    /**
     * Construtor
     *
     * @param RestService $rest
     * @param Api $defaultApi
     */
    public function __construct(RestService $rest, Api $defaultApi = null)
    {
        $this->rest = $rest;
        $this->defaultApi = $defaultApi;
    }

    /**
     * Retorna o resource baseado em um Api.
     *
     * @param Api $api
     * @param string $name
     * @return ResourceInterface
     */
    public function getResource(Api $api, $name)
    {
        return new Resource($this->rest, $api->getResource($name));
    }

    /**
     * Retorna o resource baseado na Api default.
     *
     * @param string $name
     * @return ResourceInterface
     */
    public function get($name)
    {
        return new Resource($this->rest, $this->defaultApi->getResource($name));
    }
}
