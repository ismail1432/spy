<?php

namespace Eniams\Spy\Property;

use Eniams\Spy\Assertion\SpyAssertion;
use Eniams\Spy\Exception\UndefinedContextException;
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

        return $this->doCheck($initial, $current, $classInfo, $properties);
    }

    /**
     * object $initial and object $current will be compared to know if $initial was modified in a specifc $context.
     *
     * @param object $initial
     * @param object $current
     */
    public function isModifiedInContext(PropertyCheckerContextInterface $initial, PropertyCheckerContextInterface $current, array $context = []): bool
    {
        return $this->isModified($initial, $current, $context);
    }

    /**
     * object $initial and object $current will be compared to know if $initial was modified in a specifc $context.
     *
     * @param object $initial
     * @param object $current
     */
    public function isModifiedForProperties($initial, $current, array $propertiesToCheck = []): bool
    {
        SpyAssertion::isComparable($initial, $current);

        $classInfo = $this->getCacheClassInfo()->getClassInfo($initial);

        $properties = $this->filterProperties($classInfo->getProperties(), $propertiesToCheck);

        return $this->doCheck($initial, $current, $classInfo, $properties);
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
     */
    public function getPropertiesModified($initial, $current, array $context = []): array
    {
        SpyAssertion::isComparable($initial, $current);

        $classInfo = $this->getCacheClassInfo()->getClassInfo($initial);

        $properties = $this->getFilteredProperties($initial, $classInfo->getProperties(), $context);

        return $this->doExtractPropertiesModified($initial, $current, $classInfo, $properties);
    }

    public function doExtractPropertiesModified($initial, $current, $classInfo, $properties)
    {
        $propertiesModified = [];

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
     * Return modified properties even they are excluded with the black list strategy.
     *
     * @param $initial
     * @param $current
     *
     * @return PropertyState[]
     */
    public function getPropertiesModifiedWithoutBlackListContext($initial, $current)
    {
        SpyAssertion::isComparable($initial, $current);

        $classInfo = $this->getCacheClassInfo()->getClassInfo($initial);

        return $this->doExtractPropertiesModified($initial, $current, $classInfo, $classInfo->getProperties());
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

    /**
     * @param \ReflectionProperty[]
     */
    private function doCheck($initial, $current, $classInfo, array $properties = []): bool
    {
        foreach ($properties as $property) {
            if ($this->isPropertyModified($initial, $current, $property->getName(), $classInfo)) {
                return true;
            }
        }

        return false;
    }

    private function compareCollection(array $first, array $second): array
    {
        return array_udiff($first, $second, static function () use ($first, $second) {
            return strcmp(spl_object_hash($first), spl_object_hash($second));
        });
    }

    /**
     * @param $properties \ReflectionProperty[]
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
        $propertiesExtractedFromContext = [];

        foreach ($context as $contextName) {
            if (null === $propertiesToCheck = $contextProperties[$contextName] ?? null) {
                throw new UndefinedContextException(sprintf('There is no properties for context %s', $contextName));
            }
            $propertiesExtractedFromContext = array_merge($propertiesExtractedFromContext, $propertiesToCheck);
        }

        return $this->filterProperties($properties, $propertiesExtractedFromContext);
    }

    /**
     * @param $properties \ReflectionProperty[]
     *
     * @return \ReflectionProperty[]
     */
    private function filterProperties(array $properties, array $propertyToExtract): array
    {
        return array_filter($properties, static function (\ReflectionProperty $property) use ($propertyToExtract) {
            return \in_array($property->getName(), $propertyToExtract);
        });
    }

    /**
     * Filter properties with the good strategy, Blacklist, Context or no strategy.
     *
     * @param $object
     * @param $properties \ReflectionProperty[]
     *
     * @return \ReflectionProperty[]
     */
    private function getFilteredProperties($object, array $properties = [], array $context = []): array
    {
        if ($object instanceof PropertyCheckerContextInterface && [] !== $context) {
            return $this->filterPropertiesInContext($object, $properties, $context);
        }

        if ($object instanceof PropertyCheckerBlackListInterface) {
            return $this->filterBlackListedProperties($object, $properties);
        }

        return $properties;
    }
}
