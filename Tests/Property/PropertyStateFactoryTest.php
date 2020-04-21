<?php

use Eniams\Spy\Property\PropertyState;
use Eniams\Spy\Property\PropertyStateFactory;
use Eniams\Spy\Tests\Fixtures\Children;
use Eniams\Spy\Tests\Fixtures\GrandParent;
use Eniams\Spy\Tests\Fixtures\Root;
use PHPUnit\Framework\TestCase;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
class PropertyStateFactoryTest extends TestCase
{
    /**
     * @dataProvider propertyStateProvider
     */
    public function testcreatePropertyStateIsModified(
        $initialValue,
        $updatedValue,
        $property,
        $addRoot = false,
        $updateRoot = false
    ) {
        $grandPa = (new GrandParent())->setName($initialValue);
        $grandPaUpdated = (new GrandParent())->setName($updatedValue);

        if ($addRoot) {
            $grandPaUpdated->setRoot(new Root());
        }

        if ($updateRoot) {
            $grandPa->setRoot($initialValue);
            $grandPaUpdated->setRoot($updatedValue);
        }

        $propertyState = PropertyStateFactory::createPropertyState($property, $grandPa, $grandPaUpdated);

        $this->assertTrue($propertyState->isModified());
        $this->assertEquals($initialValue, $propertyState->getInitialValue());
        $this->assertEquals($updatedValue, $propertyState->getCurrentValue());
        $this->assertEquals(GrandParent::class, $propertyState->getFqcn());

        $this->assertInstanceOf(PropertyState::class, $propertyState);
    }

    public function testArrayIsModified()
    {
        $foo = new Root();
        $fooUpdated = new Root();
        $fooUpdated->addChildren((new Children())->setName('boy'));

        $propertyState = PropertyStateFactory::createPropertyState('childrens', $foo, $fooUpdated);

        $this->assertTrue($propertyState->isModified());
        $this->assertEquals([], $propertyState->getInitialValue());
        $this->assertEquals([(new Children())->setName('boy')], $propertyState->getCurrentValue());
        $this->assertEquals(Root::class, $propertyState->getFqcn());

        $this->assertInstanceOf(PropertyState::class, $propertyState);
    }

    public function propertyStateProvider(): iterable
    {
        return [
            ['grand Pa', 'grand Pa updated', 'name'],
            ['dude', 'dude updated', 'name'],
            [null, (new Root()), 'root', true],
            [(new Root())->setName('root name'), (new Root())->setName('root name updated'), 'root', true, true],
            [(new Root())->setName('root name'), (new Root())->setName('root name updated'), 'root', true, true, true],
        ];
    }
}
