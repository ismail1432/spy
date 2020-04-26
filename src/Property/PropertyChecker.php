<?php

namespace Eniams\Spy\Property;

use Eniams\Spy\Assertion\SpyAssertion;
use Eniams\Spy\Reflection\CacheClassInfoTrait;
use Eniams\Spy\Reflection\ClassInfo;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
class PropertyChecker
{
    use CacheClassInfoTrait;

    /**
     * object $initial and object $current will be compared to know if $initial was modified.
     *
     * @param object $initial
     * @param object $current
     */
    public function isModified($initial, $current): bool
    {
        SpyAssertion::isComparable($initial, $current);

        $classInfo = $this->getCacheClassInfo()->getClassInfo($initial);

        if ($this->containsBlackListedProperties($initial)) {
            // Avoid to check if $initial implements PropertyCheckerBlackListInterface for each loop.
            return $this->checkIsModifiedWithBlackListedProperties($initial, $current, $classInfo);
        }

        foreach ($classInfo->getProperties() as $property) {
            $propertyName = $property->getName();

            if ($this->isPropertyModified($initial, $current, $propertyName, $classInfo)) {
                return true;
            }
        }

        return false;
    }

    /**
     * $property of object $initial and object $current will be compared to know if the property was modified.
     *
     * @param object $initial
     * @param object $current
     */
    public function isPropertyModified($initial, $current, string $propertyName, ClassInfo $classInfo = null): bool
    {
        SpyAssertion::isComparable($initial, $current);

        if (null === $classInfo) {
            $classInfo = $this->getCacheClassInfo()->getClassInfo($initial);
        }

        $extracted = new ValueExtractor($initial, $current, $propertyName, $classInfo);

        $initialValue = $extracted->getInitialValue();
        $currentValue = $extracted->getCurrentValue();

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

    /**
     * object $initial and object $current will be compared to know if $initial was modified except for black listed properties.
     *
     * @param object $initial
     * @param object $current
     */
    private function checkIsModifiedWithBlackListedProperties($initial, $current, ClassInfo $classInfo)
    {
        foreach ($classInfo->getProperties() as $property) {
            $propertyName = $property->getName();

            if ($this->isPropertyBlackListed($initial, $propertyName)) {
                continue;
            }

            if ($this->isPropertyModified($initial, $current, $propertyName, $classInfo)) {
                return true;
            }
        }

        return false;
    }

    public function getPropertiesModified($initial, $current, string $propertyName, ClassInfo $classInfo = null)
    {
        SpyAssertion::isComparable($initial, $current);

        $propertiesModified = [];

        if (null === $classInfo) {
            $classInfo = $this->getCacheClassInfo()->getClassInfo($initial);
        }

        $extracted = new ValueExtractor($initial, $current, $propertyName, $classInfo);

        $initialValue = $extracted->getInitialValue();
        $currentValue = $extracted->getCurrentValue();

        if ($currentValue instanceof \Doctrine\Common\Collections\Collection) {
            if ([] !== $this->compareCollection($currentValue->toArray(), $initialValue->toArray())) {
                $propertiesModified[] = PropertyState::create(get_class($initial), $propertyName, $initialValue, $propertyName);
            }
        }

        if ($initialValue != $currentValue) {
            $propertiesModified[] = PropertyState::create(get_class($initial), $propertyName, $initialValue, $propertyName);
        }

        return $propertiesModified;
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
