<?php

namespace Eniams\Spy\Property;

/**
 * All properties defined in the array will skipped when checking if the object was modified.
 * You can pass also a context to define if you want to skip in a specific context.
 *
 * @example
 * Suppose you return ['partial' => ['firstName', 'updatedAt'];
 * When passing the context PropertyChecker::isModified(['partial']) properties `firstName` and `updatedAt`
 * will not be checked.
 *
 * @see PropertyChecker::isModified().
 *
 * @author Smaïne Milianni <contact@smaine.me>
 */
interface PropertyCheckerBlackListInterface
{
    public static function propertiesBlackList(): array;
}
