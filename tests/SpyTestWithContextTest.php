<?php

namespace Eniams\Spy\Tests;

use Eniams\Spy\Spy;
use Eniams\Spy\Tests\Fixtures\ContentBlackListedFoo;
use Eniams\Spy\Tests\Fixtures\FooWithContext;
use PHPUnit\Framework\TestCase;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
class SpyTestWithContextTest extends TestCase
{
    public function testIsModifiedUpdateTitleForProperties()
    {
        $toSpy = new FooWithContext('title', 'content');

        $spied = new Spy($toSpy);

        $toSpy->title = 'Update title';

        // If no context given we filter on all properties
        $this->assertTrue($spied->isModified());
        $this->assertFalse($spied->isNotModified());

        // Pass context to check only content
        $this->assertFalse($spied->isModifiedInContext(['context_check_content']));

        // Pass context to check only title
        $this->assertTrue($spied->isModifiedInContext(['context_check_title']));

        // Pass the property to check dynamically to check only content
        $this->assertFalse($spied->isModifiedForProperties(['content']));

        // Pass the property to check dynamically to check only title
        $this->assertTrue($spied->isModifiedForProperties(['title']));
    }

    public function testIsModifiedUpdateContentForProperties()
    {
        $toSpy = new FooWithContext('title', 'content');

        $spied = new Spy($toSpy);

        $toSpy->content = 'Update content';

        // If no context given we filter on all properties
        $this->assertTrue($spied->isModified());
        $this->assertFalse($spied->isNotModified());

        // Pass context to check only content
        $this->assertTrue($spied->isModifiedInContext(['context_check_content']));

        // Pass context to check only title
        $this->assertFalse($spied->isModifiedInContext(['context_check_title']));
    }

    public function testGetModifiedPropertiesInContext()
    {
        $toSpy = new FooWithContext('title', 'content');

        $spied = new Spy($toSpy);

        $toSpy->content = 'Update content';

        // Pass context to check only content
        $propertyModified = $spied->getPropertiesModifiedInContext(['context_check_content']);

        $this->assertCount(1, $propertyModified);
        $this->assertEquals('content', $propertyModified[0]->getPropertyName());
        $this->assertEquals('content', $propertyModified[0]->getInitialValue());
        $this->assertEquals('Update content', $propertyModified[0]->getCurrentValue());

        $propertyModified = $spied->getPropertiesModifiedInContext(['context_check_title']);

        $this->assertCount(0, $propertyModified);
    }

    public function testGetPropertiesModifiedWithBlackListContext()
    {
        $toSpy = new ContentBlackListedFoo('title', 'content');
        $spied = new Spy($toSpy);

        $toSpy->content = 'Update content';

        // False as content is excluded from properties to check
        $this->assertFalse($spied->isModified());
        $this->assertCount(0, $spied->getModifiedProperties());

        // getPropertiesModifiedWithBlackListContext return excluded properties
        $this->assertCount(1, $spied->getPropertiesModifiedWithoutBlackListContext());

        $toSpy->title = 'Update title';
        // True as title is not excluded from properties to check
        $this->assertTrue($spied->isModified());
        $this->assertCount(1, $spied->getModifiedProperties());
    }
}
