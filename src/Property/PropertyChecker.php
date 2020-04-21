<?php

namespace Eniams\Spy\Property;

use Eniams\Spy\Reflection\CacheClassInfo;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
class PropertyChecker
{
    /** @var CacheClassInfo */
    private $cacheClassInfo;

    public function isPropertyModified($initial, $current, string $property): bool
    {
        $propertyReflected =
            $this->getCacheClassInfo()->getClassInfo($initial)
                ->getReflectionClass()
                ->getProperty($property);

        $propertyReflected->setAccessible(true);
        $initialValue = $propertyReflected->getValue($initial);
        $currentValue = $propertyReflected->getValue($current);

        if ($currentValue instanceof \Doctrine\Common\Collections\Collection) {
            $diff = $this->compareCollection($currentValue->toArray(), $initialValue->toArray());
            if ([] !== $diff) {
                return true;
            }
        }

        if ($initialValue != $currentValue) {
            return true;
        }

        return false;
    }

    public function isModified($initial, $current): bool
    {
        $classInfo = $this->getCacheClassInfo()->getClassInfo($initial);

        foreach ($classInfo->getProperties() as $property) {
            $propertyReflected =
                $classInfo->getReflectionClass()
                    ->getProperty($property->getName())
            ;
            $propertyReflected->setAccessible(true);

            $initialValue = $propertyReflected->getValue($initial);
            $currentValue = $propertyReflected->getValue($current);

            if ($currentValue instanceof \Doctrine\Common\Collections\Collection) {
                $diff = $this->compareCollection($currentValue->toArray(), $initialValue->toArray());
                if ($diff !== []) {
                    return true;
                }
                continue;
            } elseif ($initialValue != $currentValue) {
                return true;
            }
        }

        return false;
    }

    public function compareCollection(array $first, array $second): array
    {
        return array_udiff($first, $second, static function () use ($first, $second) {
            return strcmp(spl_object_hash($first), spl_object_hash($second));
        });
    }

    /**
     * @param object $toClone
     * @param object $cloned
     *
     * @return object
     *
     * @throws \ReflectionException
     */
    public function doClone($toClone, $cloned)
    {
        $classInfo = $this->getCacheClassInfo()->getClassInfo($toClone);

        foreach ($classInfo->getProperties() as $property) {
            $propertyReflected = $classInfo->getReflectionClass()->getProperty($property->getName());
            $propertyReflected->setAccessible(true);
            $value = $propertyReflected->getValue($toClone);
            $propertyReflected->setValue($cloned, $value);
        }

        return $cloned;
    }

    private function getCacheClassInfo(): CacheClassInfo
    {
        return $this->cacheClassInfo = $this->cacheClassInfo ?: new CacheClassInfo();
    }
}
