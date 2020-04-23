<?php

namespace Eniams\Spy\Tests\Fixtures;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
class Root
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
}
