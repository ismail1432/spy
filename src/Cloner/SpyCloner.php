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

        foreach ($classInfo->getProperties() as $property) {
            $propertyReflected = $classInfo->getReflectionClass()->getProperty($property->getName());
            $propertyReflected->setAccessible(true);
            $value = $propertyReflected->getValue($toClone);

            // Load Doctrine Proxy
            if ($value instanceof \Doctrine\ORM\Proxy\Proxy) {
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
}
