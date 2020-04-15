<?php

use Eniams\Spy\Spy;
use Eniams\Spy\SpyBase;
use PHPUnit\Framework\TestCase;

/**
 * @author Smaïne Milianni <contact@smaine.me>
 */
final class SpyBaseTest extends TestCase
{
    private $spyBase;
    private $foo;
    private $bar;

    protected function setUp(): void
    {
        $this->spyBase = new SpyBase();

        $this->foo = new Foo();
        $this->bar = new Bar();

        $spiced = [
            'fooId' => $this->foo,
            'barId' => $this->bar,
        ];

        foreach ($spiced as $id => $toSpy) {
            $this->spyBase->add($id, $toSpy);
        }
    }

    /**
     * @dataProvider toSpyIdProvider
     */
    public function testAll($id)
    {
        $this->assertArrayHasKey($id, $this->spyBase->all());
    }

    /**
     * @dataProvider toSpyIdProvider
     */
    public function testGet($id)
    {
        $spied = $this->spyBase->get($id);

        $this->assertNotNull($spied);
        $this->assertInstanceOf(Spy::class, $spied);
    }

    /**
     * @dataProvider toSpyIdProvider
     */
    public function testRemove($id)
    {
        $this->spyBase->remove($id);

        $all = $this->spyBase->all();

        $this->assertArrayNotHasKey($id, $all);
    }

    /**
     * @dataProvider toSpyProvider
     */
    public function testSet($id, $toSpy)
    {
        $this->spyBase->set($id, $toSpy);

        $all = $this->spyBase->all();

        $this->assertArrayHasKey($id, $all);
    }

    public function toSpyIdProvider()
    {
        return [
            ['fooId'],
            ['barId'],
        ];
    }

    public function toSpyProvider()
    {
        return [
            ['bazId', new Foo()],
            ['dudeId', new Bar()],
        ];
    }
}

class Foo
{
}

class Bar
{
}
