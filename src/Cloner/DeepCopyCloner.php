<?php

namespace Eniams\Spy\Cloner;

use DeepCopy\DeepCopy;

/**
 * Cloner that use the famous DeepCopy library: https://github.com/myclabs/DeepCopy.
 *
 * @author Smaïne Milianni <contact@smaine.me>
 */
class DeepCopyCloner implements ClonerInterface
{
    /**
     * {@inheritdoc}
     */
    public function doClone($object)
    {
        return (new DeepCopy())->copy($object);
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object): bool
    {
        return $object instanceof DeepCopyClonerInterface;
    }
}
