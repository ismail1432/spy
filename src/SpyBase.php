<?php

namespace Eniams\Spy;

/**
 * This is the Spy base, this class will contains all spied object.
 *
 * @author Smaïne Milianni <contact@smaine.me>
 */
final class SpyBase
{
    /**
     * @var iterable<SpyInterface>
     */
    private $spies = [];

    /**
     * Add an object to spy in the spy base.
     *
     * @param object $toSpy
     */
    public function add(string $id, $toSpy): void
    {
        if (!array_key_exists($id, $this->spies)) {
            $this->spies[$id] = new Spy($toSpy);
        }
    }

    /**
     * Get an spied object by its id.
     */
    public function get(string $id): ?Spy
    {
        return $this->spies[$id] ?? null;
    }

    /**
     * Set an object to spy, can also be use to replace a Spied object.
     *
     * @param object $toSpy
     */
    public function set(string $id, $toSpy): Spy
    {
        return $this->spies[$id] = new Spy($toSpy);
    }

    /**
     * Remove a Spied object by its id.
     */
    public function remove(string $id): void
    {
        if (array_key_exists($id, $this->spies)) {
            unset($this->spies[$id]);
        }
    }

    /**
     * @return iterable<SpyInterface>
     */
    public function all()
    {
        return $this->spies;
    }
}
