<?php

namespace Eniams\Spy\Tests\Fixtures;

class GrandSon
{
    private $name;

    public function getName()
    {
        return $this->name;
    }

    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }
}
