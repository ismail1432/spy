<?php

namespace Eniams\Spy;

use Eniams\Spy\Cloner\ChainCloner;
use Eniams\Spy\Cloner\DeepCopyCloner;
use Eniams\Spy\Cloner\SpyCloner;
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
    public function __construct($current, ChainCloner $cloner = null)
    {
        $this->current = $current;
        try {
            $cloner = $cloner ?? $this->getCloner();
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

    public function isModifiedForProperties(array $properties): bool
    {
        return $this->getPropertyChecker()->isModifiedForProperties($this->initial, $this->current, $properties);
    }

    public function isModifiedInContext(array $context): bool
    {
        return $this->getPropertyChecker()->isModifiedInContext($this->initial, $this->current, $context);
    }

    public function isPropertyModified(string $property): bool
    {
        return $this->getPropertyChecker()->isPropertyModified($this->getInitial(), $this->getCurrent(), $property);
    }

    /**
     * @return PropertyState[]
     */
    public function getModifiedProperties(): array
    {
        return $this->getPropertyChecker()->getPropertiesModified($this->initial, $this->current);
    }

    /**
     * Return modified properties even they are excluded with the black list strategy.
     *
     * @see PropertyCheckerBlackListInterface
     *
     * @return PropertyState[]
     */
    public function getPropertiesModifiedWithoutBlackListContext(): array
    {
        return $this->getPropertyChecker()->getPropertiesModifiedWithoutBlackListContext($this->initial, $this->current);
    }

    /**
     * @return PropertyState[]
     */
    public function getPropertiesModifiedInContext(array $context): array
    {
        return $this->getPropertyChecker()->getPropertiesModifiedInContext($this->initial, $this->current, $context);
    }

    public function getPropertyState(string $property): PropertyState
    {
        return $this->getPropertyStateFactory()::createPropertyState($property, $this->getInitial(), $this->getCurrent());
    }

    private function getCloner(): ChainCloner
    {
        return new ChainCloner([new DeepCopyCloner(), new SpyCloner()]);
    }

    private function getPropertyStateFactory(): PropertyStateFactory
    {
        return $this->propertyStateFactory = $this->propertyStateFactory ?: new PropertyStateFactory();
    }

    private function getPropertyChecker()
    {
        return $this->propertyChecker = $this->propertyChecker ?: new PropertyChecker();
    }
}
