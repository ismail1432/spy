<?php

namespace Eniams\Spy\Tests\Fixtures;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
class Children
{
    private $name;

    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }
}
