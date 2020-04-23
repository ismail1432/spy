<?php

namespace Eniams\Spy;

use Eniams\Spy\Cloner\ChainCloner;
use Eniams\Spy\Exception\UncopiableException;
use Eniams\Spy\Property\PropertyChecker;
use Eniams\Spy\Property\PropertyState;
use Eniams\Spy\Property\PropertyStateFactory;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
final class Spy
{
    /**
     * Object To Spy.
     *
     * @var object
     */
    private $current;

    /**
     * Initial state of the object, before modification.
     *
     * @var object
     */
    private $initial;

    /**
     * @var PropertyStateFactory
     */
    private $propertyStateFactory;

    /**
     * @var PropertyChecker
     */
    private $propertyChecker;

    /**
     * $current is the object to spy, the cloner will be resolve in the $cloner.
     *
     * @param object $current
     */
    public function __construct($current, ChainCloner $cloner)
    {
        $this->current = $current;
        try {
            $this->initial = $cloner->doClone($current);
        } catch (\Exception $e) {
            throw new UncopiableException($e->getMessage());
        }
    }

    /**
     * Get object before change.
     *
     * @return object
     */
    public function getInitial()
    {
        return $this->initial;
    }

    /**
     * Get current object affected by change.
     *
     * @return object
     */
    public function getCurrent()
    {
        return $this->current;
    }

    public function isModified(): bool
    {
        return $this->getPropertyChecker()->isModified($this->getInitial(), $this->getCurrent());
    }

    public function isNotModified(): bool
    {
        return !$this->getPropertyChecker()->isModified($this->getInitial(), $this->getCurrent());
    }

    public function isPropertyModified(string $property)
    {
        return $this->getPropertyChecker()->isPropertyModified($this->getInitial(), $this->getCurrent(), $property);
    }

    public function getPropertyState(string $property): PropertyState
    {
        return $this->getPropertyStateFactory()::createPropertyState($property, $this->getInitial(), $this->getCurrent());
    }

    private function getPropertyStateFactory(): PropertyStateFactory
    {
        return $this->propertyStateFactory = $this->propertyStateFactory ?: new PropertyStateFactory();
    }

    public function getPropertyChecker()
    {
        return $this->propertyChecker = $this->propertyChecker ?: new PropertyChecker();
    }

    // @Todo Implement method that returns an array with the modified properties
    public function getModifiedProperties()
    {
    }

    // @Todo Dispatch an event when the given property is modified
    public function spyProperty(string $property)
    {
    }

    // @Todo Dispatch an event when the given method is called
    public function spyMethod(string $method)
    {
    }
}
