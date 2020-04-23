<?php

use Eniams\Spy\Property\PropertyChecker;
use Eniams\Spy\Tests\Fixtures\FixtureProviderTrait;
use Eniams\Spy\Tests\Fixtures\GrandParent;
use Eniams\Spy\Tests\Fixtures\Root;
use PHPUnit\Framework\TestCase;

class PropertyCheckerTest extends TestCase
{
    use FixtureProviderTrait;

    /**
     * @var PropertyChecker
     */
    private $propertyChecker;

    public function setUp(): void
    {
        $this->propertyChecker = new PropertyChecker();
    }

    /**
     * @dataProvider fixtureProvider
     */
    public function testIsModifiedWithoutModification($fixture)
    {
        /** @var GrandParent $fixture */
        $copy = $fixture;
        $this->assertFalse($this->propertyChecker->isModified($fixture, $copy));
    }

    /**
     * @dataProvider fixtureProvider
     */
    public function testIsModifiedGrandPaModification($fixture)
    {
        /* @var GrandParent $fixture */
        $this->assertTrue($this->propertyChecker->isModified($fixture, (new GrandParent())->setName('Updated Name')));
    }

    /**
     * @dataProvider fixtureProvider
     */
    public function testIsModifiedRootNameModification($fixture)
    {
        /* @var GrandParent $fixture */
        $this->assertTrue($this->propertyChecker->isModified($fixture->getRoot(), (new Root())->setName('Bob')));
    }

    /**
     * @dataProvider fixtureProvider
     */
    public function testIsModifiedRootChildrenModification($fixture)
    {
        /* @var GrandParent $fixture */
        $this->assertTrue($this->propertyChecker->isModified($fixture->getRoot()->getChildren()[1], $fixture->getRoot()->getChildren()[0]->setName('chase')));
    }
}
