<?php

namespace Eniams\Spy\Assertion;

use Eniams\Spy\Exception\UncomparableException;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
final class SpyAssertion
{
    /**
     * Full qualified namespace comparaison.
     *
     * @param object $firstObject
     * @param object $secondObject
     */
    public static function sameFqcn($firstObject, $secondObject): bool
    {
        return get_class($firstObject) === get_class($secondObject);
    }

    /**
     * Check if the both given object can be compared.
     *
     * @param object $firstObject
     * @param object $secondObject
     *
     * @throws UncomparableException
     */
    public static function isComparable($firstObject, $secondObject): bool
    {
        if (self::sameFqcn($firstObject, $secondObject)) {
            return true;
        }

        throw new UncomparableException(sprintf('Cannot compare %s and %s because object are different', get_class($firstObject), get_class($secondObject)));
    }
}
