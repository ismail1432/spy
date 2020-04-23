<?php

namespace Eniams\Spy;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
trait SpyTrait
{
    /**
     * @see SpyInterface::getIdentifier()
     */
    public function getIdentifier(): string
    {
        $fqcn = get_class($this);

        if (null !== $method = \method_exists($this, 'getUuid') ? 'getUuid' : null) {
            return $fqcn.$this->{$method}();
        }

        if (null !== $method = \method_exists($this, 'getId') ? 'getId' : null) {
            return $fqcn.$this->{$method}();
        }

        throw new \Exception(sprintf('Unable to define the getIdentifier, maybe implement it in %s', $fqcn));
    }
}
