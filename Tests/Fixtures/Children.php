<?php

namespace Eniams\Spy\Tests\Fixtures;

use Eniams\Spy\Cloner\SpyClonerInterface;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
class Children implements SpyClonerInterface
{
    private $name;

    private $grandson;

    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getGrandson()
    {
        return $this->grandson;
    }

    public function setGrandson($grandson): void
    {
        $this->grandson = $grandson;
    }

    public function getIdentifier(): string
    {
        return 12345;
    }
}
