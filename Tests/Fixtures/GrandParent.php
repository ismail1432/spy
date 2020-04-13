<?php

namespace Eniams\Spy\Tests\Fixtures;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
class GrandParent
{
    private $name;
    private $root;

    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setRoot(Root $root): self
    {
        $this->root = $root;

        return $this;
    }

    public function getRoot(): ?Root
    {
        return $this->root;
    }
}
