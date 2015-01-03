<?php

namespace Broda\Core\Provider\Symfony\Validator;


use Pimple\Container;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintValidatorFactoryInterface;
use Symfony\Component\Validator\ConstraintValidatorInterface;

class ConstraintValidatorFactory implements ConstraintValidatorFactoryInterface
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var array
     */
    protected $validators;

    function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Given a Constraint, this returns the ConstraintValidatorInterface
     * object that should be used to verify its validity.
     *
     * @param Constraint $constraint The source constraint
     *
     * @return ConstraintValidatorInterface
     */
    public function getInstance(Constraint $constraint)
    {
        $name = $constraint->validatedBy();

        if (isset($this->validators[$name])) {
            return $this->validators[$name];
        }

        $this->validators[$name] = $this->createValidator($name);

        return $this->validators[$name];
    }

    /**
     * Returns the validator instance
     *
     * @param string $name
     * @return ConstraintValidator
     */
    private function createValidator($name)
    {
        if (isset($this->container[$name])) {
            return $this->container[$name];
        }

        return new $name();
    }

} 