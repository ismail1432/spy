<?php

use Eniams\Spy\Cloner\SpyClonerInterface;
use Eniams\Spy\Property\PropertyChecker;
use Eniams\Spy\Property\PropertyCheckerBlackListInterface;
use Eniams\Spy\Property\PropertyState;
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

    /**
     * @dataProvider modifiedPropertiesProvider
     */
    public function testGetModifiedProperties($toSpy, $spied, $propertyExpected)
    {
        $propertiesModified = $this->propertyChecker->getPropertiesModified($toSpy, $spied, $propertyExpected);

        $this->assertIsArray($propertiesModified);
        $this->assertCount(1, $propertiesModified);
        $first = $propertiesModified[0];
        $this->assertInstanceOf(PropertyState::class, $first);

        $this->assertEquals($propertyExpected, $first->getPropertyName());
    }

    public function modifiedPropertiesProvider()
    {
        $grandPa = $this->getGrandPaFixture();

        yield [$grandPa, (new GrandParent())->setName('Updated Name'), 'name'];
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
