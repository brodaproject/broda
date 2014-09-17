<?php

namespace Broda\Component\Rest;

use Broda\Component\Rest\Filter\Expr as FilterExpr;
use Broda\Component\Rest\Filter\FilterInterface;
use Broda\Component\Rest\Filter\Incorporator\IncorporatorFactory;
use Broda\Component\Rest\Filter\Param as FilterParam;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Selectable;
use Doctrine\ORM\QueryBuilder as OrmQueryBuilder;
use Doctrine\DBAL\Query\QueryBuilder as DbalQueryBuilder;
use JMS\Serializer\Serializer;
use JMS\Serializer\SerializerBuilder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Classe RestService
 *
 * @author Raphael Hardt <raphael.hardt@gmail.com>
 */
class RestService
{

    /**
     * Neste modo, os Totalizables irão fazer 2 SELECTs: um para retornar
     * o total de registros sem filtragem e sem limitação de paginação,
     * e outro para retornar o total de registros sem limitação, com filtragem.
     *
     * É um modo mais lento, evite se possível.
     */
    const TOTALIZABLE_ALL = 100;

    /**
     * Neste modo, os Totalizables irão fazer apenas 1 SELECT, para
     * retornar o total de registros sem limitação de paginação, mas
     * com filtragem, e o total sem filtragem será substituido por este.
     *
     * É o padrão, porém alguns plugins como DataTables não irá
     * funcionar o 'totalFiltered'.
     */
    const TOTALIZABLE_ONLY_FILTERED = 101;

    /**
     * Neste modo, os Totalizables não farão nenhum SELECT a mais,
     * e retornarão uma previsão de quantos registros tem na tabela
     * baseados nas limitações de paginação.
     *
     * Use quando não interessa mostrar o total de registros ou
     * quando a tabela conter muitos registros.
     */
    const TOTALIZABLE_UNKNOWN = 102;

    /**
     *
     * @var Serializer
     */
    private $serializer;

    /**
     *
     * @var SerializerBuilder
     */
    protected $serializerBuilder;

    /**
     * @var IncorporatorFactory
     */
    protected $incorporatorFactory;

    /**
     * Total de registros que o repositório contém.
     *
     * Só é usado para filters que implementam {@link TotalizableInterface}.
     * Serve como um cache para evitar que sejam feitos 2 querys
     * somente para retornar o número de registros filtrados e o número de
     * registros limitados e filtrados.
     *
     * Defina este valor se você tem certeza quantos registros tem na tabela
     * de antemão.
     *
     * @var int
     */
    private $totalRecords = null;

    /**
     * Defina para TRUE para evitar que sejam feitos 2 querys no banco
     * pra trazer o total quando o filter implementar {@link TotalizableInterface}
     *
     * @var int
     */
    private $totalizableMode = self::TOTALIZABLE_ALL;

    /**
     * Constructor.
     *
     */
    public function __construct()
    {
        $this->serializerBuilder = new SerializerBuilder();
        $this->incorporatorFactory = new IncorporatorFactory($this);
    }

    public function setTotalizableMode($mode)
    {
        $this->totalizableMode = $mode;
        return $this;
    }

    public function getTotalizableMode()
    {
        return $this->totalizableMode;
    }

    public function setTotalRecords($total)
    {
        $this->totalRecords = (int)$total;
        return $this;
    }

    /**
     * Usado para fazer um lazy load do serializer.
     * Ele vai pegar a configuração prévia do builder e criar um
     * serializer novo com elas.
     *
     * @return Serializer
     */
    protected function getSerializer()
    {
        if (null === $this->serializer) {
            $this->serializer = $this->serializerBuilder->build();
        }
        return $this->serializer;
    }

    /**
     * Configura o serializer.
     *
     * @return SerializerBuilder
     * @throws \LogicException
     */
    public function configureSerializer()
    {
        if (null !== $this->serializer) {
            throw new \LogicException('Serializer já foi instanciado. '
                    . 'Configure antes de criar um objeto ou serializar.');
        }
        return $this->serializerBuilder;
    }

    /**
     * Serializes the object to a required format.
     *
     * Internal. Return a RestResponse instead.
     *
     * @param mixed $data
     * @param string $format
     * @return string
     */
    public function formatOutput($data, $format)
    {
        return $this->getSerializer()->serialize($data, $format);
    }

    /**
     * Creates a object from request data
     *
     * @param Request $request
     * @param string $class
     * @return mixed
     */
    public function createObjectFromRequest(Request $request, $class)
    {
        // we must get the raw content, since the deserialization need it raw
        $data = $request->getContent();
        return $this->createObject($data, $class, $request->getContentType());
    }

    /**
     * Creates a object from array data
     *
     * @param string $data
     * @param string $class
     * @param string $format
     * @return mixed
     */
    public function createObject($data, $class, $format = 'json')
    {
        return $this->getSerializer()->deserialize($data, $class, $format);
    }

    /**
     * Filters a collection and return data filtered by request
     *
     * @param Selectable|OrmQueryBuilder|DbalQueryBuilder|array $collection
     * @param FilterInterface $filter
     * @param array $fieldMap
     * @return array
     * @throws \UnexpectedValueException
     */
    public function filter($collection, FilterInterface $filter, array $fieldMap = array())
    {
        if (is_array($collection)) {
            $collection = new ArrayCollection($collection);
        }

        $incorporator = $this->incorporatorFactory->getIncorporator($collection);
        $incorporator->setFieldMap($fieldMap);
        return $filter->getOutputResponse($incorporator->incorporate($collection, $filter));

    }

}
