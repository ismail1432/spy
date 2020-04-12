<?php

namespace Eniams;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
final class Spy
{
    /**
     * @var object
     */
    private $reference;

    /**
     * @var object
     */
    private $referenceAtInitialState;

    /**
     * @var object
     */
    private $cloned;

    public function __construct(object $reference)
    {
        $this->reference = $reference;

        if(false === $this->isCloneable($reference)) {
            throw new UncloneableException(sprintf('Unable to clone %s', \get_class($reference)));
        }

        $this->cloned = clone $this->reference;
        $this->referenceAtInitialState = \unserialize(\serialize($this->reference));
    }

    public function getCloned(): object
    {
        return $this->cloned;
    }

    public function getReference(): object
    {
        return $this->reference;
    }

    public function getReferenceAtInitialState(): object
    {
        return $this->referenceAtInitialState;
    }

    public function isModified(): bool
    {
        return $this->referenceAtInitialState != $this->cloned;
    }

    // @Todo Implement method that returns an array with the modified properties
    public function getModifiedProperties()
    {
    }

    private function isCloneable(object $object): bool
    {
        return (new \ReflectionClass($object))->isCloneable();
    }
}
