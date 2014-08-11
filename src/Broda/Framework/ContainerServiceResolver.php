<?php

namespace Broda\Framework;

use Pimple\Container;

class ContainerServiceResolver
{
    const SERVICE_PATTERN = "/[A-Za-z0-9\._\-]+:[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/";

    private $sc;

    public function __construct(Container $container)
    {
        $this->sc = $container;
    }

    /**
     * Returns true if the string is a valid service method representation.
     *
     * @param string $name
     *
     * @return bool
     */
    public function isValid($name)
    {
        return is_string($name) && preg_match(static::SERVICE_PATTERN, $name);
    }

    /**
     * Returns a callable given its string representation.
     *
     * @param string $name
     *
     * @return array A callable array
     *
     * @throws \InvalidArgumentException In case the method does not exist.
     */
    public function convertCallback($name)
    {
        list($service, $method) = explode(':', $name, 2);

        if (!isset($this->sc[$service])) {
            throw new \InvalidArgumentException(sprintf('Service "%s" does not exist.', $service));
        }

        return array($this->sc[$service], $method);
    }

    /**
     * Returns a callable given its string representation if it is a valid service method.
     *
     * @param string $name
     *
     * @return array A callable array
     *
     * @throws \InvalidArgumentException In case the method does not exist.
     */
    public function resolveCallback($name)
    {
        return $this->isValid($name) ? $this->convertCallback($name) : $name;
    }
}
