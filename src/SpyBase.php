<?php

namespace Eniams\Spy;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
class SpyBase
{
    /**
     * @var Spy[]
     */
    private $spies = [];

    /**
     * @param object $toSpy
     */
    public function add(string $id, $toSpy): void
    {
        if (!array_key_exists($id, $this->spies)) {
            $this->spies[$id] = new Spy($toSpy);
        }
    }

    public function get(string $id): ?Spy
    {
        return $this->spies[$id] ?? null;
    }

    /**
     * @param object $toSpy
     */
    public function set(string $id, $toSpy): Spy
    {
        return $this->spies[$id] = new Spy($toSpy);
    }

    public function remove(string $id): void
    {
        if (array_key_exists($id, $this->spies)) {
            unset($this->spies[$id]);
        }
    }

    /**
     * @return Spy[]
     */
    public function all()
    {
        return $this->spies;
    }
}
