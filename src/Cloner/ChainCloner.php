<?php

namespace Eniams\Spy\Cloner;

use Eniams\Spy\Exception\UndefinedClonerException;

/**
 * Handle the copy of the spied object.
 *
 * @author Smaïne Milianni <contact@smaine.me>
 */
final class ChainCloner
{
    /**
     * @var iterable<ClonerInterface>
     *
     * @internal
     */
    private $cloners;

    /**
     * @param ClonerInterface[] $cloners
     */
    public function __construct(iterable $cloners)
    {
        $this->cloners = $cloners;
    }

    /**
     * Clones the given object.
     *
     * @param mixed $object
     *
     * @return mixed
     */
    public function doClone($object)
    {
        foreach ($this->cloners as $cloner) {
            if ($cloner->supports($object)) {
                return $cloner->doClone($object);
            }
        }

        throw new UndefinedClonerException(sprintf('Unable to resolve the Cloner, Did you forgot to implement %s or %s ?', DeepCopyClonerInterface::class, SpyClonerInterface::class));
    }
}
