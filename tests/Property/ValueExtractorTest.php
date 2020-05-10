<?php

namespace Eniams\Spy\Tests\Property;

use Eniams\Spy\Property\ValueExtractor;
use PHPUnit\Framework\TestCase;

class ValueExtractorTest extends TestCase
{
    /**
     * @dataProvider valueProvider
     */
    public function testGetValue($data)
    {
        $initialValue = ['username' => 'John', 'lastname' => 'Doe', 'city' => 'Paris'];

        $toSpy = (new FooSpied())->initialize($initialValue);
        $spied = (new FooSpied())->initialize($data);

        foreach ($data as $property => $value) {
            $extracted = new ValueExtractor($toSpy, $spied, $property);
            $this->assertEquals($initialValue[$property], $extracted->getInitialValue());
            $this->assertEquals($value, $extracted->getCurrentValue());
        }
    }

    public function valueProvider()
    {
        yield [['username' => 'Smaone', 'lastname' => 'Eniams', 'city' => 'Algiers']];
        yield [['username' => 'James', 'lastname' => 'Bond', 'city' => 'London']];
    }
}

class FooSpied
{
    private $username;
    private $lastname;
    private $city;

    public function initialize(array $data): self
    {
        $this->username = $data['username'];
        $this->lastname = $data['lastname'];
        $this->city = $data['city'];

        return $this;
    }
}
