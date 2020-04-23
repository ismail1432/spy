<?php

namespace Eniams\Spy\Assertion;

use Eniams\Spy\Exception\UncomparableException;

final class SpyAssertion
{
    public static function sameFqcn($firstObject, $secondObject)
    {
        return get_class($firstObject) === get_class($secondObject);
    }

    public static function isComparable($firstObject, $secondObject)
    {
        if (self::sameFqcn($firstObject, $secondObject)) {
            return true;
        }

        throw new UncomparableException(sprintf('Cannot compare %s and %s because object are different', get_class($firstObject), get_class($secondObject)));
    }
}
