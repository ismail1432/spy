<?php

namespace Eniams\Spy\Tests\Property;

use Eniams\Spy\Exception\UndefinedContextException;
use Eniams\Spy\Property\PropertyChecker;
use Eniams\Spy\Property\PropertyState;
use Eniams\Spy\Tests\Fixtures\ContentBlackListedFoo;
use Eniams\Spy\Tests\Fixtures\FixtureProviderTrait;
use Eniams\Spy\Tests\Fixtures\FooWithContext;
use Eniams\Spy\Tests\Fixtures\GrandParent;
use Eniams\Spy\Tests\Fixtures\Root;
use Eniams\Spy\Tests\Fixtures\TitleBlackListedFoo;
use PHPUnit\Framework\TestCase;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
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
        $this->assertTrue($this->propertyChecker->isModified($fixture->getRoot()->getChildren()[1],
            $fixture->getRoot()->getChildren()[0]->setName('chase')));
    }

    public function testGetModifiedPropertiesNameAndRoot()
    {
        $toSpy = $this->getGrandPaFixture();

        // Update Name and Don't Have Root object
        $spied = (new GrandParent())->setName('Updated Name');

        $expectedPropertiesModified = [
            'name',
            'root',
        ];

        $propertiesModified = $this->propertyChecker->getPropertiesModified($toSpy, $spied);
        $propertiesNameModified = [];

        $this->assertIsArray($propertiesModified);
        $this->assertCount(2, $propertiesModified);
        foreach ($propertiesModified as $key => $propertyState) {
            $this->assertInstanceOf(PropertyState::class, $propertyState);
            $propertiesNameModified[] = $propertyState->getPropertyName();
        }

        $this->assertEquals($expectedPropertiesModified, $propertiesNameModified);
        $this->assertEquals($propertiesModified, [
            PropertyState::create(get_class($toSpy), 'name', 'grand Pa', 'Updated Name'),
            PropertyState::create(get_class($toSpy), 'root', $toSpy->getRoot(), null),
        ]);
    }

    /**
     * @dataProvider fixtureProvider
     */
    public function testGetModifiedPropertiesChildrenName($fixture)
    {
        $toSpy = $fixture->getRoot()->getChildren()[1];
        $spied = $fixture->getRoot()->getChildren()[0]->setName('chase');

        $propertiesModified = $this->propertyChecker->getPropertiesModified($toSpy, $spied);

        /** @var PropertyState $propertyState */
        $propertyState = $propertiesModified[0];
        $this->assertCount(1, $propertiesModified);
        $this->assertInstanceOf(PropertyState::class, $propertyState);

        $this->assertEquals(\Eniams\Spy\Tests\Fixtures\Children::class, $propertyState->getFqcn());
        $this->assertEquals('name', $propertyState->getPropertyName());
        $this->assertEquals('chase', $propertyState->getCurrentValue());
        $this->assertEquals('Sara', $propertyState->getInitialValue());
    }

    public function testTitleBlacklistedPropertiesShouldNotBeChecked()
    {
        $toSpy = new TitleBlackListedFoo('chapter 1', 'Foo content');
        $copy = new TitleBlackListedFoo('chapter 2', 'Foo content');

        // False because we don't check `title` as it's returned by propertiesBlackList
        $this->assertFalse($this->propertyChecker->isModified($toSpy, $copy));
    }

    public function testContentBlacklistedPropertiesShouldNotBeChecked()
    {
        $toSpy = new ContentBlackListedFoo('chapter 1', 'Foo content');
        $copy = new ContentBlackListedFoo('chapter 1', 'Baz content');

        // False because we don't check `content` as it's returned by propertiesBlackList
        $this->assertFalse($this->propertyChecker->isModified($toSpy, $copy));
    }

    public function testContentPropertiesWithContext()
    {
        $toSpy = new FooWithContext('chapter 1', 'Foo content');
        $copy = new FooWithContext('chapter 1', 'Baz content');

        // False because we don't check `content` in the context
        $this->assertFalse($this->propertyChecker->isModifiedInContext($toSpy, $copy, ['context_check_title']));

        // True because we check `content` because it's passed in the context
        $this->assertTrue($this->propertyChecker->isModifiedInContext($toSpy, $copy, ['context_check_content']));
    }

    public function testTitlePropertiesWithContext()
    {
        $toSpy = new FooWithContext('chapter 1', 'Foo content');
        $copy = new FooWithContext('chapter 1 updated', 'Foo content');

        // False because we don't check `title` in the context
        $this->assertFalse($this->propertyChecker->isModifiedInContext($toSpy, $copy, ['context_check_content']));

        // True because we check `title` because it's passed in the context
        $this->assertTrue($this->propertyChecker->isModifiedInContext($toSpy, $copy, ['context_check_title']));
    }

    public function testTitleAndContentPropertiesWithMultipleContext()
    {
        $toSpy = new FooWithContext('chapter 1', 'Foo content');
        $copy = new FooWithContext('chapter 1 updated', 'Bar content');

        $this->assertTrue($this->propertyChecker->isModifiedInContext($toSpy, $copy, ['context_check_title', 'context_check_content']));
    }

    public function testGetModifiedPropertiesWithoutBlackListContext()
    {
        $toSpy = new TitleBlackListedFoo('chapter 1', 'Foo content');
        $copy = new TitleBlackListedFoo('chapter 2', 'Foo content');

        // `title` should be returned in property modified event it's excluded from property to check
        $this->assertCount(1, $this->propertyChecker->getPropertiesModifiedWithoutBlackListContext($toSpy, $copy));
    }

    public function testGetModifiedPropertiesInContext()
    {
        $toSpy = new FooWithContext('chapter 1', 'Foo content');
        $copy = new FooWithContext('chapter 2', 'Foo content');

        // `title` is not returned as it's not defined in the context
        $this->assertCount(0, $this->propertyChecker->getPropertiesModifiedInContext($toSpy, $copy, ['context_check_content']));

        // `title` is  returned as it's defined in the context
        $propertiesModified = $this->propertyChecker->getPropertiesModifiedInContext($toSpy, $copy, ['context_check_title']);
        $this->assertCount(1, $propertiesModified);

        /** @var PropertyState $propertyState */
        $propertyState = $propertiesModified[0];
        $this->assertInstanceOf(PropertyState::class, $propertyState);
        $this->assertEquals('title', $propertyState->getPropertyName());
        $this->assertEquals('chapter 1', $propertyState->getInitialValue());
        $this->assertEquals('chapter 2', $propertyState->getCurrentValue());
    }

    public function testIsModifiedForProperties()
    {
        $toSpy = new FooWithContext('chapter 1', 'Foo content');
        $copy = new FooWithContext('chapter 2', 'Foo content');

        // True because we check `title` property dynamically
        $this->assertTrue($this->propertyChecker->isModifiedForProperties($toSpy, $copy, ['title']));

        // False because we check `title` property dynamically
        $this->assertFalse($this->propertyChecker->isModifiedForProperties($toSpy, $copy, ['content']));
    }

    public function testUndefinedContextException()
    {
        $contextName = 'yolo';

        $this->expectException(UndefinedContextException::class);
        $this->expectExceptionMessage(sprintf('There is no properties for context %s', $contextName));

        $toSpy = new FooWithContext('chapter 1', 'Foo content');
        $copy = new FooWithContext('chapter 2', 'Foo content');

        $this->propertyChecker->getPropertiesModifiedInContext($toSpy, $copy, [$contextName]);
    }
}
