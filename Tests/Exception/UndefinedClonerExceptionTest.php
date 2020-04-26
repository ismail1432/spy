<?php

namespace Eniams\Spy\Tests\Exception;

use Eniams\Spy\Cloner\ChainCloner;
use Eniams\Spy\Cloner\DeepCopyCloner;
use Eniams\Spy\Cloner\DeepCopyClonerInterface;
use Eniams\Spy\Cloner\SpyClonerInterface;
use Eniams\Spy\Exception\UndefinedClonerException;

class UndefinedClonerExceptionTest
{
    public function testSupportUndefinedClonerExceptionWhenNoClonerAreDefined()
    {
        $this->expectException(UndefinedClonerException::class);
        $this->expectExceptionMessage(sprintf('Unable to resolve the Cloner, Did you forgot to implement %s or %s ?', DeepCopyClonerInterface::class, SpyClonerInterface::class));

        $chainCloner = new ChainCloner([]);

        $chainCloner->supports(new \stdClass());
    }

    public function testSupportUndefinedClonerExceptionWhenClonerIsNotResolved()
    {
        $this->expectException(UndefinedClonerException::class);
        $this->expectExceptionMessage(sprintf('Unable to resolve the Cloner, Did you forgot to implement %s or %s ?', DeepCopyClonerInterface::class, SpyClonerInterface::class));

        $chainCloner = new ChainCloner([new DeepCopyCloner()]);

        $chainCloner->supports(new Dummy());
    }

    public function testDoCloneUndefinedClonerExceptionWhenNoClonerAreDefined()
    {
        $this->expectException(UndefinedClonerException::class);
        $this->expectExceptionMessage(sprintf('Unable to resolve the Cloner, Did you forgot to implement %s or %s ?', DeepCopyClonerInterface::class, SpyClonerInterface::class));

        $chainCloner = new ChainCloner([]);

        $chainCloner->doClone(new \stdClass());
    }

    public function testDoCloneUndefinedClonerExceptionWhenClonerIsNotResolved()
    {
        $this->expectException(UndefinedClonerException::class);
        $this->expectExceptionMessage(sprintf('Unable to resolve the Cloner, Did you forgot to implement %s or %s ?', DeepCopyClonerInterface::class, SpyClonerInterface::class));

        $chainCloner = new ChainCloner([new DeepCopyCloner()]);

        $chainCloner->doClone(new Dummy());
    }
}

class Dummy implements SpyClonerInterface
{
    public function getIdentifier(): string
    {
        return '';
    }
}
