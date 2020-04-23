<?php

namespace Eniams\Spy\Property;

use Eniams\Spy\Reflection\CacheClassInfoTrait;
use Eniams\Spy\Reflection\ClassInfo;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
class PropertyChecker
{
    use CacheClassInfoTrait;

    public function isPropertyModified($initial, $current, string $property): bool
    {
        if ($this->checkValue($initial, $current, $this->getCacheClassInfo()->getClassInfo($initial), $property)) {
            return true;
        }

        return false;
    }

    // Avoid to check if $initial implements PropertyCheckerBlackListInterface for each loop.
    public function checkIsModifiedWithBlackListedProperties($initial, $current, ClassInfo $classInfo)
    {
        foreach ($classInfo->getProperties() as $property) {
            $propertyName = $property->getName();

            if ($this->isPropertyBlackListed($initial, $propertyName)) {
                continue;
            }

            if ($this->checkValue($initial, $current, $classInfo, $propertyName)) {
                return true;
            }
        }

        return false;
    }

    public function isModified($initial, $current): bool
    {
        $classInfo = $this->getCacheClassInfo()->getClassInfo($initial);

        if ($this->containsBlackListedProperties($initial)) {
            return $this->checkIsModifiedWithBlackListedProperties($initial, $current, $classInfo);
        }

        foreach ($classInfo->getProperties() as $property) {
            $propertyName = $property->getName();

            if ($this->checkValue($initial, $current, $classInfo, $propertyName)) {
                return true;
            }
        }

        return false;
    }

    private function checkValue($initial, $current, ClassInfo $classInfo, string $propertyName): bool
    {
        $propertyReflected =
            $classInfo->getReflectionClass()
                ->getProperty($propertyName);
        $propertyReflected->setAccessible(true);

        $initialValue = $propertyReflected->getValue($initial);
        $currentValue = $propertyReflected->getValue($current);

        if ($currentValue instanceof \Doctrine\Common\Collections\Collection) {
            if ([] !== $this->compareCollection($currentValue->toArray(), $initialValue->toArray())) {
                return true;
            }
        }

        if ($initialValue != $currentValue) {
            return true;
        }

        return false;
    }

    private function compareCollection(array $first, array $second): array
    {
        return array_udiff($first, $second, static function () use ($first, $second) {
            return strcmp(spl_object_hash($first), spl_object_hash($second));
        });
    }

    private function isPropertyBlackListed($object, string $property): bool
    {
        return \in_array($property, $object::propertiesBlackList(), true);
    }

    private function containsBlackListedProperties($object): bool
    {
        return $object instanceof PropertyCheckerBlackListInterface;
    }
}
