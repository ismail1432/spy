<?php

namespace Eniams\Spy\Tests\Fixtures;

use Eniams\Spy\Cloner\SpyClonerInterface;
use Eniams\Spy\Property\PropertyCheckerBlackListInterface;

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
