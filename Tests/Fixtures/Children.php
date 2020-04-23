<?php

namespace Eniams\Spy\Tests\Fixtures;

use Eniams\Spy\Cloner\SpyClonerInterface;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
class Children implements SpyClonerInterface
{
    private $name;

    public function setName($name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getIdentifier(): string
    {
        return 12345;
    }
}
