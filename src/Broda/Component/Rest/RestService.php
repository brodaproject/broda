<?php

namespace Broda\Component\Rest;

use Broda\Component\Rest\Filter\Expr as FilterExpr;
use Broda\Component\Rest\Filter\FilterInterface;
use Broda\Component\Rest\Filter\Incorporator\IncorporatorFactory;
use Broda\Component\Rest\Filter\Incorporator\IncorporatorInterface;
use Broda\Component\Rest\Filter\Param as FilterParam;
use Doctrine\Common\Collections\ArrayCollection;
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
     * Defina para TRUE para evitar que sejam feitos 2 querys no banco
     * pra trazer o total quando o filter implementar {@link TotalizableInterface}
     *
     * @var int
     */
    private $totalizableMode = IncorporatorInterface::TOTALIZABLE_ALL;

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
     * @param mixed $collection
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
