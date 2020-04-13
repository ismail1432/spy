<?php

namespace Eniams\Spy;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
class SpyBase
{
    /**
     * @var array|Spy
     */
    private $spies = [];

    public function add(string $id, object $toSpy): void
    {
        if(!array_key_exists($id, $this->spies)) {
            $this->spies[$id] = new Spy($toSpy);
        }
    }

    public function get(string $id): ?Spy
    {
        return $this->spies[$id] ?? null;
    }

    public function set(string $id, $toSpy): void
    {
        $this->spies[$id] = new Spy($toSpy);
    }

    public function remove(string $id)
    {
        if(array_key_exists($id, $this->spies)) {
            unset($this->spies[$id]);
        }
    }

    public function all()
    {
        return $this->spies;
    }
}
