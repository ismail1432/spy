<?php

namespace Eniams\Spy\Cloner;

/**
 * Handle the copy of the spied object.
 *
 * @author contact@smaine.me
 */
final class ChainCloner
{
    /**
     * @var iterable<ClonerInterface>
     *
     * @internal
     */
    public $cloners;

    /**
     * @param ClonerInterface[] $cloners
     */
    public function __construct(iterable $cloners)
    {
        $this->cloners = $cloners;
    }

    /**
     * Is the given $object supported by the cloner?
     */
    public function supports($data): bool
    {
        foreach ($this->cloners as $cloner) {
            if ($cloner->supports($data)) {
                return true;
            }
        }

        return false;
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
    }
}
