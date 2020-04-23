<?php

use Eniams\Spy\Cloner\ChainCloner;
use Eniams\Spy\Cloner\DeepCopyCloner;
use Eniams\Spy\Cloner\SpyCloner;
use Eniams\Spy\Property\PropertyState;
use Eniams\Spy\Spy;
use Eniams\Spy\Tests\Fixtures\FixtureProviderTrait;
use Eniams\Spy\Tests\Fixtures\GrandParent;
use Eniams\Spy\Tests\Fixtures\GrandSon;
use PHPUnit\Framework\TestCase;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
final class SpyTest extends TestCase
{
    use FixtureProviderTrait;

    /**
     * @var ChainCloner
     */
    private $cloner;

    public function setUp(): void
    {
        $this->cloner = new ChainCloner([new DeepCopyCloner(), new SpyCloner()]);
    }

    /**
     * @dataProvider fixtureProvider
     */
    public function testIsModifiedWithoutModification($fixture)
    {
        /** @var GrandParent $fixture */
        $spied = new Spy($fixture, $this->cloner);

        $this->assertTrue($spied->isNotModified());
        $this->assertFalse($spied->isModified());

        /** @var PropertyState $propertyState */
        $propertyState = $spied->getPropertyState('name');

        $this->assertFalse($propertyState->isModified());
        $this->assertEquals('grand Pa', $propertyState->getInitialValue());
        $this->assertEquals('grand Pa', $propertyState->getCurrentValue());
        $this->assertEquals(GrandParent::class, $propertyState->getFqcn());

        $this->assertEquals('name', $propertyState->getPropertyName());
    }

    /**
     * @dataProvider fixtureProvider
     */
    public function testIsModifiedGrandPaModification($fixture)
    {
        /** @var GrandParent $fixture */
        $spied = new Spy($fixture, $this->cloner);

        $fixture->setName('update name');

        $this->assertTrue($spied->isModified());
        $this->assertFalse($spied->isNotModified());

        /** @var PropertyState $propertyState */
        $propertyState = $spied->getPropertyState('name');

        $this->assertTrue($propertyState->isModified());
        $this->assertFalse(!$propertyState->isModified());
        $this->assertEquals('grand Pa', $propertyState->getInitialValue());
        $this->assertEquals('update name', $propertyState->getCurrentValue());
        $this->assertEquals(GrandParent::class, $propertyState->getFqcn());
        $this->assertEquals('name', $propertyState->getPropertyName());
    }

    /**
     * @dataProvider fixtureProvider
     */
    public function testIsModifiedRootNameModification($fixture)
    {
        /** @var GrandParent $fixture */
        $spied = new Spy($fixture, $this->cloner);
        $fixture->getRoot()->setName('update name');
        $rootBeforeChange = $this->getRootFixture();

        $this->assertTrue($spied->isModified());
        $this->assertFalse($spied->isNotModified());

        /** @var PropertyState $propertyState */
        $propertyState = $spied->getPropertyState('root');

        $this->assertTrue($propertyState->isModified());
        $this->assertFalse(!$propertyState->isModified());
        $this->assertEquals($rootBeforeChange, $propertyState->getInitialValue());
        $this->assertEquals($fixture->getRoot(), $propertyState->getCurrentValue());
        $this->assertEquals(GrandParent::class, $propertyState->getFqcn());
        $this->assertEquals('root', $propertyState->getPropertyName());
    }

    /**
     * @dataProvider fixtureProvider
     */
    public function testIsModifiedRootChildrenModification($fixture)
    {
        /** @var GrandParent $fixture */
        $spied = new Spy($fixture, $this->cloner);
        $fixture->getRoot()->removeChildren(1);
        $rootBeforeChange = $this->getRootFixture();

        $this->assertTrue($spied->isModified());
        $this->assertFalse($spied->isNotModified());

        /** @var PropertyState $propertyState */
        $propertyState = $spied->getPropertyState('root');

        $this->assertTrue($propertyState->isModified());
        $this->assertFalse(!$propertyState->isModified());
        $this->assertEquals($rootBeforeChange, $propertyState->getInitialValue());
        $this->assertEquals($fixture->getRoot(), $propertyState->getCurrentValue());
        $this->assertEquals(GrandParent::class, $propertyState->getFqcn());
        $this->assertEquals('root', $propertyState->getPropertyName());
    }

    /**
     * @dataProvider fixtureProvider
     */
    public function testIsModifiedChildrenModification($fixture)
    {
        /** @var GrandParent $fixture */
        $spied = new Spy($fixture, $this->cloner);
        $rootBeforeChange = $this->getRootFixture();
        $fixture->getRoot()->getChildren()[0]->setName('Update children Name');

        $this->assertTrue($spied->isModified());
        $this->assertFalse($spied->isNotModified());

        /** @var PropertyState $propertyState */
        $propertyState = $spied->getPropertyState('root');

        $this->assertTrue($propertyState->isModified());
        $this->assertFalse(!$propertyState->isModified());
        $this->assertEquals($rootBeforeChange, $propertyState->getInitialValue());
        $this->assertEquals($fixture->getRoot(), $propertyState->getCurrentValue());
        $this->assertEquals(GrandParent::class, $propertyState->getFqcn());
        $this->assertEquals('root', $propertyState->getPropertyName());
    }

    /**
     * @dataProvider fixtureProvider
     */
    public function testIsModifiedGrandSonModification($fixture)
    {
        // Grandson
        $grandson = (new GrandSon())->setName('grand son');

        /* @var GrandParent $fixture */
        $fixture->getRoot()->getChildren()[0]->setGrandson($grandson);
        $spied = new Spy($fixture, $this->cloner);

        $grandson->setName('Update grand son');

        $this->assertTrue($spied->isModified());
        $this->assertFalse($spied->isNotModified());
    }
}
