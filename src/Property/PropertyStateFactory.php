<?php

namespace Eniams\Spy\Property;

use Eniams\Spy\Assertion\SpyAssertion;
use Eniams\Spy\Reflection\CacheClassInfo;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
final class PropertyStateFactory
{
    public static function createPropertyState(string $property, $initial, $current): PropertyState
    {
        SpyAssertion::isComparable($initial, $current);

        $reflection = (new CacheClassInfo())
            ->getClassInfo($initial)
            ->getReflectionClass();

        $propertyReflected = $reflection->getProperty($property);
        $propertyReflected->setAccessible(true);

        return PropertyState::create(get_class($initial), $property, $propertyReflected->getValue($initial), $propertyReflected->getValue($current));
    }
}
