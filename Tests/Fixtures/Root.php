<?php

namespace Eniams\Spy\Tests\Fixtures;

use Eniams\Spy\SpyInterface;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
class Root implements SpyInterface
{
    private $name;

    private $childrens = [];

    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return$this->name;
    }

    public function addChildren(Children $children): self
    {
        $this->childrens[] = $children;

        return $this;
    }

    public function removeChildren(int $key): void
    {
        if (array_key_exists($key, $this->childrens)) {
            unset($this->childrens[$key]);
        }
    }

    public function getChildren(): ?array
    {
        return $this->childrens;
    }

    public function getIdentifier(): string
    {
        return 12345;
    }
}
