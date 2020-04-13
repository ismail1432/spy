<?php

namespace Eniams\Spy\Cloner;

/**
 * To be implemented by the object to spy if you want to track changes on object stored in property.
 * Any property returned in getPropertiesObjectToClone will be cloned even if the property is an object stored in a relation.
 *
 * Suppose you have an object $a with a property $b, $b is an object that have an object $c
 * and you want to track change on $a even the change was on $c.
 * return ['b','c'] in getPropertiesObjectToClone should be enough.
 *
 * @author Smaïne Milianni <contact@smaine.me>
 */
interface SpyClonerLoadPropertyObjectInterface extends SpyClonerInterface
{
    /**
     * Properties that contains object defined in the array will be cloned too by SpyCloner::doClone().
     */
    public static function getPropertiesObjectToClone(): array;
}
