<?php

namespace Broda\Core\Controller\Annotations;

/**
 * Anotação que faz injetar um serviço do container em
 * um controller.
 *
 * Exemplo de uso:
 * <code>
 *
 * use CMS\Core\Controller\Annotations as Ctrl;
 *
 * class AlgumaPaginaController
 * {
 *     /**
 *      * Para dependencias no construtor (hard dependencies)
 *      *
 *      * (@)Ctrl\Inject("logger")
 *      *
 *     function __construct($logger)
 *     {
 *         // $logger === $container['logger']
 *     }
 *
 *     /**
 *      * Para dependencias nos setters (soft dependencies)
 *      *
 *      * (@)Ctrl\Inject("db", key="furacaophp")
 *      *
 *     function setConnection($connection)
 *     {
 *         // $connection === $container['db']['furacaophp']
 *     }
 * }
 *
 * </code>
 *
 * @Annotation
 * @Target({"METHOD"})
 */
class Inject
{
    /**
     * Nome do serviço a ser injetado no controller.
     *
     * @var string
     */
    public $value;

    /**
     * Se o serviço for um array ou um outro container,
     * define qual o key que deverá ser lido para
     * pegar o serviço a ser injetado.
     *
     * Ex: (@)Inject("servico_x", key="key_y")
     * Vai injetar: $container['servico_x']['key_y']
     *
     * @var string
     */
    public $key;

    /**
     * Se o serviço for um objeto, define qual propriedade
     * deve ser lida para pegar o serviço a ser injetado.
     *
     * Pode ser qualquer string entendida pelo {@see PropertyAccessor}.
     *
     * Ex: (@)Inject("servico_x", property="propriedade_x")
     * Vai injetar, pela ordem:
     *  - $container['servico_x']->getPropriedadeX(); ou
     *  - $container['servico_x']->hasPropriedadeX(); ou
     *  - $container['servico_x']->isPropriedadeX(); ou
     *  - $container['servico_x']->propriedade_x
     *
     * @var string
     */
    public $property;

    /**
     * Se o serviço for um objeto, define qual metodo
     * deve ser invocado para pegar o serviço a ser injetado.
     *
     * Lembrando que o método não pode ter nenhum parametro,
     * pois ele será invocado dessa forma.
     *
     * Ex: (@)Inject("servico_x", method="getServicoY")
     * Vai injetar: $container['servico_x']->getServicoY()
     *
     * @var string
     */
    public $method;
} 