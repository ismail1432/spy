<?php

namespace Eniams\Spy\Property;

use Eniams\Spy\Exception\UncomparableException;
use Eniams\Spy\Reflection\CacheClassInfo;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
final class PropertyStateFactory
{
    public static function createPropertyState(string $property, $initial, $current): PropertyState
    {
        if (get_class($initial) !== get_class($current)) {
            throw new UncomparableException(sprintf('Cannot compare %s and %s because object are different', get_class($initial), get_class($current)));
        }

        $reflection = (new CacheClassInfo())
            ->getClassInfo($initial)
            ->getReflectionClass();

        $propertyReflected = $reflection->getProperty($property);
        $propertyReflected->setAccessible(true);

        return PropertyState::create(get_class($initial), $property, $propertyReflected->getValue($initial), $propertyReflected->getValue($current));
    }
}
