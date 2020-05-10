<?php

namespace Eniams\Spy\Property;

use Eniams\Spy\Assertion\SpyAssertion;
use Eniams\Spy\Exception\UndefinedContextForBlackListPropertiesException;
use Eniams\Spy\Reflection\CacheClassInfoTrait;
use Eniams\Spy\Reflection\ClassInfo;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
class PropertyChecker
{
    use CacheClassInfoTrait;

    /**
     * @var array
     */
    private $blackListedProperties;

    /**
     * object $initial and object $current will be compared to know if $initial was modified.
     *
     * @param object $initial
     * @param object $current
     */
    public function isModified($initial, $current, array $context = []): bool
    {
        SpyAssertion::isComparable($initial, $current);

        $classInfo = $this->getCacheClassInfo()->getClassInfo($initial);

        if ($this->containsBlackListedProperties($initial)) {
            $this->initializeBlackListProperties($initial, $context);
            // Avoid to check if $initial implements PropertyCheckerBlackListInterface for each loop.
            return $this->checkIsModifiedWithBlackListedProperties($initial, $current, $classInfo, $context);
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
     * @param array  $context The to skip properties.
     *
     * For context @see PropertyCheckerBlackListInterface::propertiesBlackList
     */
    private function checkIsModifiedWithBlackListedProperties($initial, $current, ClassInfo $classInfo, array $context = [])
    {
        foreach ($classInfo->getProperties() as $property) {
            $propertyName = $property->getName();

            if ($this->isPropertyBlackListed($propertyName)) {
                continue;
            }

            if ($this->isPropertyModified($initial, $current, $propertyName, $classInfo)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param object $initial
     * @param object $current
     * @param $skipBlackListedProperties bool true by default will skip the black listed properties
     *
     * @see PropertyCheckerBlackListInterface::propertiesBlackList()
     *
     * @return PropertyState[]
     */
    public function getPropertiesModified($initial, $current, bool $skipBlackListedProperties = true, array $context = []): array
    {
        SpyAssertion::isComparable($initial, $current);

        $propertiesModified = [];

        $ignoreBlackListedProperties = $this->ignoreBlackListedProperties($initial, $skipBlackListedProperties, $context);

        $classInfo = $this->getCacheClassInfo()->getClassInfo($initial);

        foreach ($classInfo->getProperties() as $property) {
            $propertyName = $property->getName();

            if ($ignoreBlackListedProperties && $this->isPropertyBlackListed($propertyName)) {
                continue;
            }

            $extracted = new ValueExtractor($initial, $current, $propertyName, $classInfo);

            $initialValue = $extracted->getInitialValue();
            $currentValue = $extracted->getCurrentValue();

            if ($currentValue instanceof \Doctrine\Common\Collections\Collection) {
                if ([] !== $this->compareCollection($currentValue->toArray(), $initialValue->toArray())) {
                    $propertiesModified[] = PropertyState::create(get_class($initial), $propertyName, $initialValue, $currentValue);
                }
            }

            if ($initialValue != $currentValue) {
                $propertiesModified[] = PropertyState::create(get_class($initial), $propertyName, $initialValue, $currentValue);
            }
        }

        return $propertiesModified;
    }

    private function compareCollection(array $first, array $second): array
    {
        return array_udiff($first, $second, static function () use ($first, $second) {
            return strcmp(spl_object_hash($first), spl_object_hash($second));
        });
    }

    private function isPropertyBlackListed(string $property): bool
    {
        return \in_array($property, $this->blackListedProperties, true);
    }

    private function containsBlackListedProperties($object): bool
    {
        return $object instanceof PropertyCheckerBlackListInterface;
    }

    private function ignoreBlackListedProperties($object, bool $skipBlackListedProperties, array $context): bool
    {
        $ignoreProperties = false;

        if (true === $ignoreProperties = ($this->containsBlackListedProperties($object) && true === $skipBlackListedProperties)) {
            $this->initializeBlackListProperties($object, $context);
        }

        return $ignoreProperties;
    }

    private function initializeBlackListProperties(PropertyCheckerBlackListInterface $object, array $context = []): void
    {
        $blackListedProperties = $object::propertiesBlackList();
        $this->blackListedProperties = [];

        if ([] !== $context) {
            foreach ($context as $contextName) {
                if (null === $blackListedList = $blackListedProperties[$contextName] ?? null) {
                    throw new UndefinedContextForBlackListPropertiesException(sprintf('There is no properties for contex %s', $contextName));
                }
                $this->blackListedProperties = array_merge($this->blackListedProperties, $blackListedList);
            }
        }

        $this->blackListedProperties = $this->blackListedProperties ?: $blackListedProperties;
    }
}
