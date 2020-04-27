<?php

namespace Eniams\Spy\Property;

/**
 * All properties defined in the array will not be checked when checking if the object was modified.
 *
 * @see PropertyChecker::isModified().
 *
 * @author Smaïne Milianni <contact@smaine.me>
 */
interface PropertyCheckerBlackListInterface
{
    public static function propertiesBlackList(): array;
}
