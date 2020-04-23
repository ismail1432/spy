<?php

namespace Eniams\Spy\Reflection;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
trait CacheClassInfoTrait
{
    /** @var CacheClassInfo */
    private $cacheClassInfo;

    public function getCacheClassInfo(): CacheClassInfo
    {
        return $this->cacheClassInfo = $this->cacheClassInfo ?: new CacheClassInfo();
    }
}
