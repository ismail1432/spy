<?php

namespace Eniams\Spy\Cloner;

/**
 * Clone Object.
 *
 * @author contact@smaine.me
 */
interface ClonerInterface
{
    /**
     * Clones the given object.
     *
     * @param mixed $object
     *
     * @return mixed
     */
    public function doClone($object);

    /**
     * Is the given $object supported by the cloner?
     */
    public function supports($object): bool;
}
