<?php

namespace Eniams\Spy\Tests\Exception;

use Eniams\Spy\Exception\UncomparableException;
use Eniams\Spy\Property\PropertyStateFactory;
use Eniams\Spy\Tests\Fixtures\Children;
use Eniams\Spy\Tests\Fixtures\GrandParent;
use Eniams\Spy\Tests\Fixtures\Root;
use PHPUnit\Framework\TestCase;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
class UncomparableExceptionTest extends TestCase
{
    /**
     * @dataProvider fqcnProvider
     */
    public function testThrowUncomparableException($referenceInitialState, $reference)
    {
        $this->expectException(UncomparableException::class);
        $this->expectExceptionMessage(sprintf("Cannot compare %s and %s because object are different", get_class($referenceInitialState), get_class($reference)));

        PropertyStateFactory::createPropertyState('foo', $referenceInitialState, $reference);
    }

    public function fqcnProvider()
    {
        return [
          [new GrandParent(), new Children()],
          [new Children(), new GrandParent()],
          [new Root(), new Children()],
          [new Children(), new Root()],
          [new GrandParent(), new Root()],
          [new Root(), new GrandParent()],
        ];
    }
}
