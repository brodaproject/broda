<?php

namespace Broda\Component\Rest\Loader;

use Broda\Component\Rest\Annotation\Server\ResourceMethod;
use Broda\Component\Rest\Server\Resource;
use Broda\Component\Rest\Server\ResourceManager;
use Doctrine\Common\Annotations\Reader;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Loader\LoaderResolverInterface;

class AnnotationClassLoader implements LoaderInterface
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     *
     * @var ResourceManager
     */
    protected $rm;

    /**
     * Constructor.
     *
     * @param Reader $reader
     */
    public function __construct(Reader $reader, ResourceManager $rm)
    {
        $this->reader = $reader;
        $this->rm = $rm;
    }

    /**
     * Loads from annotations from a class.
     *
     * @param string      $class A class name
     * @param string|null $type  The resource type
     *
     * @throws \InvalidArgumentException When route can't be parsed
     */
    public function load($class, $type = null)
    {
        if (!class_exists($class)) {
            throw new \InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        $class = new \ReflectionClass($class);
        if ($class->isAbstract()) {
            throw new \InvalidArgumentException(sprintf('Annotations from class "%s" cannot be read as it is abstract.', $class));
        }

        /* @var $resource Resource */
        $resource = null;
        $controller = '';

        // get resource
        $annot = $this->reader->getClassAnnotation($class, 'Broda\Component\Rest\Annotation\Server\Resource');

        if (null !== $annot) {
            $path = $annot->getBasePath() . '/{'.$annot->getIdName().'}';
            if ($annot->getFormat()) {
                $path .= '.' . $annot->getFormat();
            }

            $parentResource = null;
            if ($parent = $annot->getParent()) {
                if ('/' !== $parent[0]) {
                    $parent = $class->getNamespaceName() . $parent;
                }
                $parentResource = $this->load($parent);
            }

            if ($annot->getService()) {
                $controller = $annot->getService().':';
            } else {
                $controller = $class->getName().'::';
            }

            if (null !== $parentResource) {
                // todos os resources e subresources são de responsabilidade do manager
                $resource = $parentResource->subresource($path);
            } else {
                // mesmo que o resource já tenha sido definido, o resource manager
                // sempre retorna a mesma instancia
                $resource = $this->rm->resource($path);
            }
        }

        if (null !== $resource) {
            foreach ($class->getMethods() as $method) {
                $routeType = $this->getDefaultRouteType($method->getName());

                foreach ($this->reader->getMethodAnnotations($method) as $methodAnnot) {
                    if ($methodAnnot instanceof ResourceMethod) {
                        $routeType = $methodAnnot->getName();
                    }
                }

                // esse try-catch serve para que, quando haja referencia circular entre os
                // resources, o match (que por consequencia será chamado duas vezes pro mesmo
                // resource) não lance exceptions de "rota já definida".
                try {
                    $resource->match($routeType, $controller.$method->getName());
                } catch (\LogicException $e) {
                    // TODO: colocar alguma opção de "silent" no resource manager, para
                    // evitar este problema
                }
            }
        }

        return $resource;
    }

    /**
     * Retorna o routeType padrão para um nome de método.
     *
     * Por ex, por padrão, o método 'all' é mapeado para o routeType 'all'.
     * Esses padrões podem ser alterados no Resource::$defaultMethods, onde
     * os keys são os routeType e os values são os nomes dos métodos.
     *
     * Se não encontrado, o routeType padrão será o nome do método.
     *
     * @param string $methodName
     * @return string
     */
    private function getDefaultRouteType($methodName)
    {
        $defaults = Resource::$defaultMethods;
        foreach ($defaults as $routeType => $method) {
            if ($methodName === $method) {
                return $routeType;
            }
        }
        // irá funcionar também se o método for exatamente o nome do routetype (all, get, post, etc...)
        return $methodName;
    }

    public function supports($resource, $type = null)
    {
        return is_string($resource) && preg_match('/^(?:\\\\?[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)+$/', $resource) && (!$type || 'annotation' === $type);
    }

    public function getResolver()
    {

    }

    public function setResolver(LoaderResolverInterface $resolver)
    {

    }

}
