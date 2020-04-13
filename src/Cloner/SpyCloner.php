<?php

namespace Eniams\Spy\Cloner;

use Eniams\Spy\Reflection\CacheClassInfoTrait;

class SpyCloner implements ClonerInterface
{
    use CacheClassInfoTrait;

    private $initialized = false;

    private $cachedClassInfo = [];

    private $propertyObjectToClone = [];

    /**
     * {@inheritdoc}
     */
    public function doClone($toClone)
    {
        $cloned = clone $toClone;
        $classInfo = $this->getCacheClassInfo()->getClassInfo($cloned);

        $this->initializePropertyObjectToLoad($toClone);

        foreach ($classInfo->getProperties() as $property) {
            $propertyName = $property->getName();
            $propertyReflected = $classInfo->getReflectionClass()->getProperty($property->getName());
            $propertyReflected->setAccessible(true);
            $value = $propertyReflected->getValue($toClone);

            // Clone deeper properties given by `SpyClonerLoadPropertyObjectInterface::getPropertiesObjectToClone`
            if (\in_array($propertyName, $this->propertyObjectToClone)) {
                $value = $this->cloneVar($value);
            }
            $propertyReflected->setValue($cloned, $value);
        }

        return $cloned;
    }

    private function cloneArray($array): array
    {
        $copied = [];
        foreach ($array as $key => $toCopy) {
            $copied[$key] = $this->cloneVar($toCopy);
        }

        return $copied;
    }

    private function cloneVar($toCopy)
    {
        if (is_array($toCopy)) {
            return $this->cloneArray($toCopy);
        }

        if (is_object($toCopy)) {
            return $this->doClone($toCopy);
        }

        return $toCopy;
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

    public function initializePropertyObjectToLoad($object): void
    {
        if (!$this->initialized) {
            $this->initialized = true;
            $this->propertyObjectToClone = $this->supportCloneObjectProperties($object) ?
                /* @var SpyClonerLoadPropertyObjectInterface $object */
                $object::getPropertiesObjectToClone() : [];
        }
    }
}
