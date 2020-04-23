<?php

namespace Eniams\Spy\Cloner;

use Eniams\Spy\SpyInterface;

/**
 * To be implemented by the object you want to spy if you want to copy your object with the \DeepCopy\DeepCopy::copy method
 * (https://github.com/myclabs/DeepCopy) via the DeepCopyCloner.
 *
 * @author Smaïne Milianni <contact@smaine.me>
 */
interface DeepCopyClonerInterface extends SpyInterface
{
}
