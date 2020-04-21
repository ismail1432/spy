<?php

namespace Eniams\Spy\Reflection;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
class CacheClassInfo
{
    /** @var ClassInfo[] */
    private $cachedClassInfo;

    public function getClassInfo($object): ClassInfo
    {
        if (null !== $classInfo = $this->getCachedClassInfo($object)) {
            return $classInfo;
        }

        return $this->createClassInfo($object);
    }

    private function createClassInfo($object): ClassInfo
    {
        return $this->cachedClassInfo[$this->getHashFromObject($object)] = new ClassInfo($object);
    }

    private function getCachedClassInfo($object): ?ClassInfo
    {
        return $this->cachedClassInfo[$this->getHashFromObject($object)] ?? null;
    }

    private function getHashFromObject($object): string
    {
        return \function_exists('spl_object_id') ? spl_object_id($object) : spl_object_hash($object);
    }
}
