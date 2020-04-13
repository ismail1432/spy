<?php

namespace Eniams\Spy;

use Eniams\Spy\Property\PropertyStateFactory;
use Eniams\Spy\Property\PropertyState;
use Eniams\Spy\Exception\UncopiableException;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
final class Spy
{
    /**
     * @var object
     */
    private $reference;

    private $propertyStateFactory;

    /**
     * @var object
     */
    private $referenceAtInitialState;

    public function __construct($reference)
    {
        $this->reference = $reference;
        try {
            $this->referenceAtInitialState = \unserialize(\serialize($this->reference));
        }catch (\Exception $e) {
            throw new UncopiableException($e->getMessage());
        }
    }

    public function getReference()
    {
        return $this->reference;
    }

    public function getReferenceAtInitialState()
    {
        return $this->referenceAtInitialState;
    }

    public function isModified(): bool
    {
       // var_dump($this->referenceAtInitialState, $this->reference);die;
        return $this->referenceAtInitialState != $this->reference;
    }

    public function isNotModified(): bool
    {
        return $this->referenceAtInitialState == $this->reference;
    }

    // @Todo Implement method that returns an array with the modified properties
    public function getModifiedProperties()
    {
    }

    // @Todo Implement method to check if a property was modified
    public function isPropertyModified(string $property): bool
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

    public function getPropertyState(string $property): PropertyState
    {
        return $this->getPropertyStateFactory()::createPropertyState($property, $this->referenceAtInitialState, $this->reference);
    }

    private function getPropertyStateFactory(): PropertyStateFactory
    {
        return $this->propertyStateFactory = $this->propertyStateFactory ?: new PropertyStateFactory();
    }
}
