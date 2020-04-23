<?php

namespace Eniams\Spy\Cloner;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
interface SpyClonerLoadPropertyObjectInterface extends SpyClonerInterface
{
    /**
     * Properties that contains object defined in the array will be cloned too by SpyCloner::doClone().
     */
    public static function getPropertiesObjectToClone(): array;
}
