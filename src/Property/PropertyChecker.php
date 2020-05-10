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

        $properties = $this->getFilteredProperties($initial, $classInfo->getProperties(), $context);

        foreach ($properties as $property) {
            if ($this->isPropertyModified($initial, $current, $property->getName(), $classInfo)) {
                return true;
            }
        }

        return false;
    }

    /**
     * object $initial and object $current will be compared to know if $initial was modified.
     *
     * @param object $initial
     * @param object $current
     */
    public function isModifiedInContext(PropertyCheckerContextInterface $initial, PropertyCheckerContextInterface $current, array $context = []): bool
    {
        return $this->isModified($initial, $current, $context);
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
     * @param object $initial
     * @param object $current
     *
     * @return PropertyState[]
     *
     * @see PropertyCheckerBlackListInterface::propertiesBlackList()
     */
    public function getPropertiesModified($initial, $current, array $context = []): array
    {
        SpyAssertion::isComparable($initial, $current);

        $propertiesModified = [];

        $classInfo = $this->getCacheClassInfo()->getClassInfo($initial);

        $properties = $this->getFilteredProperties($initial, $classInfo->getProperties(), $context);

        foreach ($properties as $property) {
            $propertyName = $property->getName();

            $extracted = new ValueExtractor($initial, $current, $propertyName, $classInfo);

            $initialValue = $extracted->getInitialValue();
            $currentValue = $extracted->getCurrentValue();

            if ($currentValue instanceof \Doctrine\Common\Collections\Collection) {
                if ([] !== $this->compareCollection($currentValue->toArray(), $initialValue->toArray())) {
                    $propertiesModified[] = PropertyState::create(get_class($initial), $propertyName, $initialValue,
                        $currentValue);
                }
            }

            if ($initialValue != $currentValue) {
                $propertiesModified[] = PropertyState::create(get_class($initial), $propertyName, $initialValue,
                    $currentValue);
            }
        }

        return $propertiesModified;
    }

    /**
     * @param $initial
     * @param $current
     *
     * @return PropertyState[]
     */
    public function getPropertiesModifiedWithBlackListContext($initial, $current)
    {
        return $this->getPropertiesModified($initial, $current);
    }

    /**
     * @param $initial
     * @param $current
     *
     * @return PropertyState[]
     */
    public function getPropertiesModifiedInContext($initial, $current, array $context = [])
    {
        return $this->getPropertiesModified($initial, $current, $context);
    }

    private function compareCollection(array $first, array $second): array
    {
        return array_udiff($first, $second, static function () use ($first, $second) {
            return strcmp(spl_object_hash($first), spl_object_hash($second));
        });
    }

    /**
     * @param array $properties \ReflectionProperty[]
     *
     * @return \ReflectionProperty[]
     */
    private function filterBlackListedProperties(PropertyCheckerBlackListInterface $object, array $properties = []): array
    {
        $blacklistProperties = $object::propertiesBlackList();

        return array_filter($properties, static function (\ReflectionProperty $property) use ($blacklistProperties) {
            return !\in_array($property->getName(), $blacklistProperties);
        });
    }

    /**
     * @param array $properties \ReflectionProperty[]
     *
     * @return \ReflectionProperty[]
     */
    private function filterPropertiesInContext(PropertyCheckerContextInterface $object, array $properties = [], array $context = []): array
    {
        $contextProperties = $object::propertiesInContext();
        $filteredFromContext = [];

        foreach ($context as $contextName) {
            if (null === $propertiesToCheck = $contextProperties[$contextName] ?? null) {
                throw new UndefinedContextForBlackListPropertiesException(sprintf('There is no properties for contex %s', $contextName));
            }
            $filteredFromContext = array_merge($filteredFromContext, $propertiesToCheck);
        }

        return array_filter($properties, static function (\ReflectionProperty $property) use ($filteredFromContext) {
            return \in_array($property->getName(), $filteredFromContext);
        });
    }

    /**
     * Filter properties with the good strategy, Blacklist, Context or no strategy.
     *
     * @param $object
     *
     * @return \ReflectionProperty[]
     */
    private function getFilteredProperties($object, array $properties = [], array $context = []): array
    {
        if ($object instanceof PropertyCheckerBlackListInterface) {
            return $this->filterBlackListedProperties($object, $properties);
        }
        if ($object instanceof PropertyCheckerContextInterface) {
            return $this->filterPropertiesInContext($object, $properties, $context);
        }

        return $properties;
    }
}
