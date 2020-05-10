<?php

namespace Eniams\Spy\Tests\Property;

use Eniams\Spy\Cloner\SpyClonerInterface;
use Eniams\Spy\Property\PropertyChecker;
use Eniams\Spy\Property\PropertyCheckerBlackListInterface;
use Eniams\Spy\Property\PropertyState;
use Eniams\Spy\Tests\Fixtures\FixtureProviderTrait;
use Eniams\Spy\Tests\Fixtures\GrandParent;
use Eniams\Spy\Tests\Fixtures\Root;
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

    public function testContentBlacklistedPropertiesWithContext()
    {
        $toSpy = new FooBlackListedWithContext('chapter 1', 'Foo content');
        $copy = new FooBlackListedWithContext('chapter 1', 'Baz content');

        // False because we don't check `content` as it's passed in context do not check
        $this->assertFalse($this->propertyChecker->isModified($toSpy, $copy, ['context_do_not_check_content']));

        // True because we check `content` because we don't pass any context
        $this->assertTrue($this->propertyChecker->isModified($toSpy, $copy));
    }

    public function testTitleBlacklistedPropertiesWithContext()
    {
        $toSpy = new FooBlackListedWithContext('chapter 1', 'Foo content');
        $copy = new FooBlackListedWithContext('chapter 1 updated', 'Foo content');

        // False because we don't check `title` as it's passed in context do not check
        $this->assertFalse($this->propertyChecker->isModified($toSpy, $copy, ['context_do_not_check_title']));

        // True because we check `title` because we don't pass any context
        $this->assertTrue($this->propertyChecker->isModified($toSpy, $copy));
    }

    public function testTitleAndContentBlacklistedPropertiesWithMultipleContext()
    {
        $toSpy = new FooBlackListedWithContext('chapter 1', 'Foo content');
        $copy = new FooBlackListedWithContext('chapter 1 updated', 'Bar content');

        // False because we don't check `title` and `content` as they are passed in context do not check
        $this->assertFalse($this->propertyChecker->isModified($toSpy, $copy, ['context_do_not_check_title', 'context_do_not_check_content']));

        // True because we check `title` and `content` because we don't pass any context
        $this->assertTrue($this->propertyChecker->isModified($toSpy, $copy));
    }

    public function testGetModifiedPropertiesWithFalseStrict()
    {
        $toSpy = new TitleBlackListedFoo('chapter 1', 'Foo content');
        $copy = new TitleBlackListedFoo('chapter 2', 'Foo content');

        // `title` will be returned in property modified as the 3rd params ($skipBlackListedProperties) is false.
        $this->assertCount(1, $this->propertyChecker->getPropertiesModified($toSpy, $copy, false));

        /** @var PropertyState $propertyState */
        $propertyState = $this->propertyChecker->getPropertiesModified($toSpy, $copy, false)[0];
        $this->assertInstanceOf(PropertyState::class, $propertyState);
        $this->assertEquals('chapter 1', $propertyState->getInitialValue());
        $this->assertEquals('chapter 2', $propertyState->getCurrentValue());
        $this->assertEquals('title', $propertyState->getPropertyName());

        // `title` will not be returned in property modified as the it's exclude in `TitleBlackListedFoo::propertiesBlackList`
        $this->assertCount(0, $this->propertyChecker->getPropertiesModified($toSpy, $copy));
    }

    public function testGetModifiedPropertiesWithFalseStrictAndContext()
    {
        $toSpy = new FooBlackListedWithContext('chapter 1', 'Foo content');
        $copy = new FooBlackListedWithContext('chapter 2', 'Foo content');

        // `title` will not be returned in property modified even the 3rd params ($skipBlackListedProperties) is false
        // because the context exclude to check modification on `title`
        $this->assertCount(0, $this->propertyChecker->getPropertiesModified($toSpy, $copy, false, ['context_do_not_check_content']));
    }
}

class TitleBlackListedFoo implements SpyClonerInterface, PropertyCheckerBlackListInterface
{
    public $title;
    public $content;

    public function __construct($title, $content)
    {
        $this->title = $title;
        $this->content = $content;
    }

    public static function propertiesBlackList(): array
    {
        return ['title'];
    }

    public function getIdentifier(): string
    {
        return 'blackListed';
    }
}

class ContentBlackListedFoo implements SpyClonerInterface, PropertyCheckerBlackListInterface
{
    public $title;
    public $content;

    public function __construct($title, $content)
    {
        $this->title = $title;
        $this->content = $content;
    }

    public static function propertiesBlackList(): array
    {
        return ['content'];
    }

    public function getIdentifier(): string
    {
        return 'blackListed';
    }
}

class FooBlackListedWithContext implements SpyClonerInterface, PropertyCheckerBlackListInterface
{
    public $title;
    public $content;

    public function __construct($title, $content)
    {
        $this->title = $title;
        $this->content = $content;
    }

    public static function propertiesBlackList(): array
    {
        return [
            'context_do_not_check_title' => ['title'],
            'context_do_not_check_content' => ['content'],
        ];
    }

    public function getIdentifier(): string
    {
        return 'blackListed';
    }
}
