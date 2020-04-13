<?php

namespace Eniams\Spy\Property;

use Eniams\Spy\Exception\UncomparableException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessor;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
final class PropertyStateFactory
{
    private static $propertyAccessor;

    private static function getPropertyAccessor(): PropertyAccessor
    {
        return self::$propertyAccessor = self::$propertyAccessor ?: PropertyAccess::createPropertyAccessor();
    }

    public static function createPropertyState(string $property, $referenceInitialState, $reference): PropertyState
    {
        if(get_class($referenceInitialState) !== get_class($reference)) {
            throw new UncomparableException(sprintf("Cannot compare %s and %s because object are different", get_class($referenceInitialState), get_class($reference)));
        }

        $accessor = self::getPropertyAccessor();

        $initialValue = $accessor->getValue($referenceInitialState, $property);
        $currentValue = $accessor->getValue($reference, $property);

        return PropertyState::create(get_class($reference), $property, $initialValue, $currentValue);
    }
}
