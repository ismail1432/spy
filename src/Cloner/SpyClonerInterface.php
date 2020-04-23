<?php

namespace Eniams\Spy\Cloner;

use Eniams\Spy\SpyInterface;

/**
 * To be implemented by the object to spy if you want to copy your object with
 * the SpyCloner.
 * Be aware that with this interface the modification on object that is stored
 * in a property may not be tracked.
 *
 * If you want to clone the object stored in property you should implement SpyClonerLoadPropertyObjectInterface (@see SpyClonerLoadPropertyObjectInterface)
 *
 * Suppose you want to spy $foo and have a property $bar that is an object,
 * if $foo implements SpyClonerInterface changes on $foo may not be tracked.
 * You should implement SpyClonerLoadPropertyObjectInterface.
 *
 * @author Smaïne Milianni <contact@smaine.me>
 */
interface SpyClonerInterface extends SpyInterface
{
}
