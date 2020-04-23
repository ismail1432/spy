<?php

namespace Eniams\Spy\Cloner;

use Eniams\Spy\Reflection\CacheClassInfoTrait;

class SpyCloner implements ClonerInterface
{
    use CacheClassInfoTrait;

    private $cachedClassInfo = [];

    /**
     * {@inheritdoc}
     */
    public function doClone($toClone)
    {
        $cloned = clone $toClone;
        $classInfo = $this->getCacheClassInfo()->getClassInfo($cloned);

        $propertyObjectToClone = $this->supportCloneObjectProperties($toClone) ?
                $toClone::getPropertiesObjectToClone() : [];

        foreach ($classInfo->getProperties() as $property) {
            $propertyName = $property->getName();
            $propertyReflected = $classInfo->getReflectionClass()->getProperty($property->getName());
            $propertyReflected->setAccessible(true);
            $value = $propertyReflected->getValue($toClone);

            if (is_object($value) && \in_array($propertyName, $propertyObjectToClone)) {
                $value = $this->doClone($value);
            }
            $propertyReflected->setValue($cloned, $value);
        }

        return $cloned;
    }

    /**
     * {@inheritdoc}
     */
    public function supports($object): bool
    {
        return $object instanceof SpyClonerInterface;
    }

    public function supportCloneObjectProperties($object): bool
    {
        return $object instanceof SpyClonerLoadPropertyObjectInterface;
    }
}
