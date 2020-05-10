<?php

namespace Eniams\Spy\Property;

/**
 * Define properties that should be checked in a given context.
 *
 * @example
 * Suppose you return ['partial' => ['firstName', 'updatedAt'];
 * When passing the context PropertyChecker::isModifiedInContext(['partial']) Only properties `firstName` and `updatedAt`
 * will be checked.
 *
 * @see PropertyChecker::isModifiedInContext(array $context = []).
 *
 * @author Smaïne Milianni <contact@smaine.me>
 */
interface PropertyCheckerContextInterface
{
    public static function propertiesInContext(): array;
}
