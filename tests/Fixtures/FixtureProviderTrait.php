<?php

namespace Eniams\Spy\Tests\Fixtures;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
trait FixtureProviderTrait
{
    public function getGrandPaFixture()
    {
        return (new GrandParent())->setName('grand Pa')->setRoot($this->getRootFixture());
    }

    public function fixtureProvider()
    {
        $grandPa = $this->getGrandPaFixture();

        return [
            [$grandPa],
        ];
    }

    public function getRootFixture(): Root
    {
        $boy = (new Children())->setName('Jon');
        $girl = (new Children())->setName('Sara');

        $dad = (new Root())->setName('daddy')->addChildren($boy)->addChildren($girl);

        return $dad;
    }
}
