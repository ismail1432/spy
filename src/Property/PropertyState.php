<?php

namespace Eniams\Spy\Property;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
class PropertyState
{
    private $property;
    private $fqcn;
    private $initialValue;
    private $currentValue;
    private $isModified;

    public function __construct(string $fqcn, string $property, $initialValue, $currentValue)
    {
        $this->fqcn = $fqcn;
        $this->property = $property;
        $this->initialValue = $initialValue;
        $this->currentValue = $currentValue;
        $this->isModified = $this->isModified();
    }

    public static function create($fqcn, $property, $initialValue, $currentValue): self
    {
        return new self($fqcn, $property, $initialValue, $currentValue);
    }

    public function getFqcn(): string
    {
        return $this->fqcn;
    }

    public function getPropertyName(): string
    {
        return $this->property;
    }

    /**
     * @return mixed
     */
    public function getInitialValue()
    {
        return $this->initialValue;
    }

    /**
     * @return mixed
     */
    public function getCurrentValue()
    {
        return $this->currentValue;
    }

    public function isModified(): bool
    {
        return $this->isModified = $this->initialValue !== $this->currentValue;
    }
}
