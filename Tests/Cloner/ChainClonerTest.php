<?php

use PHPUnit\Framework\TestCase;

class ChainClonerTest extends TestCase
{
    /**
     * @dataProvider clonerProviderPositiveSupport
     */
    public function testSupportsReturnTrueIfInterfaceInObjectToSpyMatchTheCloner($objectToSpy, $cloner)
    {
        $cloner = new \Eniams\Spy\Cloner\ChainCloner([$cloner]);
        $this->assertTrue($cloner->supports($objectToSpy));
    }

    /**
     * @dataProvider clonerProviderNegativeSupport
     */
    public function testSupportsReturnFalseIfInterfaceNotInObjectToSpyMatchTheCloner($objectToSpy, $cloner)
    {
        $this->assertFalse($cloner->supports($objectToSpy));
    }

    public function clonerProviderPositiveSupport()
    {
        yield [new Dummy(), new \Eniams\Spy\Cloner\DeepCopyCloner()];
        yield [new Foolk(), new \Eniams\Spy\Cloner\SpyCloner()];
        yield [new Bob(), new \Eniams\Spy\Cloner\SpyCloner()];
    }

    public function clonerProviderNegativeSupport()
    {
        yield [new Dummy(), new \Eniams\Spy\Cloner\SpyCloner()];
        yield [new Foolk(), new \Eniams\Spy\Cloner\DeepCopyCloner()];
        yield [new Bob(), new \Eniams\Spy\Cloner\DeepCopyCloner()];
    }
}

class Dummy implements \Eniams\Spy\Cloner\DeepCopyClonerInterface
{
    public function getIdentifier(): string
    {
        return '';
    }
}

class Foolk implements \Eniams\Spy\Cloner\SpyClonerInterface
{
    public function getIdentifier(): string
    {
        return '';
    }
}

class Bob implements \Eniams\Spy\Cloner\SpyClonerLoadPropertyObjectInterface
{
    public function getIdentifier(): string
    {
        return '';
    }

    public static function getPropertiesObjectToClone(): array
    {
        return [];
    }
}
