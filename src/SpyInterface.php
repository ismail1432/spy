<?php

namespace Eniams\Spy;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
interface SpyInterface
{
    /**
     * Unique Identifier that can be use when stored in @see SpyBase::add().
     */
    public function getIdentifier(): string;
}
