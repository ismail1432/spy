<?php

namespace Eniams\Spy\Property;

use Eniams\Spy\Assertion\SpyAssertion;
use Eniams\Spy\Reflection\CacheClassInfoTrait;
use Eniams\Spy\Reflection\ClassInfo;

/**
 * @internal
 *
 * @author Smaïne Milianni <contact@smaine.me>
 */
final class ValueExtractor
{
    use CacheClassInfoTrait;

    /**
     * @var mixed
     */
    private $initialValue;

    /**
     * @var mixed
     */
    private $currentValue;

    /**
     * @var string
     */
    private $property;

    public function __construct($initial, $current, string $propertyName, ClassInfo $classInfo = null)
    {
        SpyAssertion::isComparable($initial, $current);

        if (null === $classInfo) {
            $classInfo = $this->getCacheClassInfo()->getClassInfo($initial);
        }

        $propertyReflected =
            $classInfo->getReflectionClass()
                ->getProperty($propertyName);
        $propertyReflected->setAccessible(true);

        $this->initialValue = $propertyReflected->getValue($initial);
        $this->currentValue = $propertyReflected->getValue($current);
    }

    /**
     * @return mixed
     */
    public function getInitialValue()
    {
        return $this->initialValue;
    }

    /**
     * @return mixed
     */
    public function getCurrentValue()
    {
        return $this->currentValue;
    }

    public function getProperty(): string
    {
        return $this->property;
    }
}
