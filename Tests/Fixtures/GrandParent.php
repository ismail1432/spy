<?php

namespace Eniams\Spy\Tests\Fixtures;

use Eniams\Spy\Cloner\SpyClonerLoadPropertyObjectInterface;
use Eniams\Spy\SpyTrait;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
class GrandParent implements SpyClonerLoadPropertyObjectInterface
{
    use SpyTrait;

    public static function getPropertiesObjectToClone(): array
    {
        return ['root', 'childrens', 'grandson'];
    }

    // Used by SpyTrait to define the Spy Id stored in SpyBase.
    public function getId()
    {
        return 290;
    }

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
