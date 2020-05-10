<?php

namespace Eniams\Spy\Tests\Fixtures;

use Eniams\Spy\Cloner\SpyClonerInterface;
use Eniams\Spy\Property\PropertyCheckerContextInterface;

class FooWithContext implements SpyClonerInterface, PropertyCheckerContextInterface
{
    public $title;
    public $content;

    public function __construct($title, $content)
    {
        $this->title = $title;
        $this->content = $content;
    }

    public static function propertiesInContext(): array
    {
        return [
            'context_check_title' => ['title'],
            'context_check_content' => ['content'],
        ];
    }

    public function getIdentifier(): string
    {
        return 'foo';
    }
}
