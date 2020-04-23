<?php

namespace Eniams\Spy\Reflection;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
final class ClassInfo
{
    /**
     * @var \ReflectionClass
     */
    private $reflectionClass;

    /**
     * @var \ReflectionProperty[]
     */
    private $properties;

    public function __construct($object)
    {
        $this->reflectionClass = new \ReflectionClass(get_class($object));
        $this->properties = $this->reflectionClass->getProperties();
    }

    public function getReflectionClass(): \ReflectionClass
    {
        return $this->reflectionClass;
    }

    /**
     * @return \ReflectionProperty[]
     */
    public function getProperties()
    {
        return $this->properties;
    }
}
