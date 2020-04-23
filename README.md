# Spy

Spy helps you to know if an object was modified and allow you to fire/listen an event when the given object is modified.  

⚠️ *This project is a work in progress.* ⚠️

## Vanilla PHP

### How it works ?

The initial object will be copied with a specific cloner then value of the initial (copied) object and the manipulated (current) object will be compared on demand to check if there is some modificiations.

#### Suppose you want to spy a `foo` object to know if it was modified :

1. Tag the class to spy with an interface that will correspond to the chosen cloner, there is 2 built-in cloners :
 -  `Eniams\Spy\Cloner\DeepCopyClonerInterface` that use the famous library [DeepCopy](https://github.com/myclabs/DeepCopy)

 or
 -  `Eniams\Spy\Cloner\SpyClonerLoadPropertyObjectInterface` or `Eniams\Spy\Cloner\SpyClonerInterface` that allows you to clone more deeper the object/array stored in properties

```php
<?php
namespace App\Entity\Foo;

class Foo implements \Eniams\Spy\Cloner\DeepCopyClonerInterface
// or SpyClonerLoadPropertyObjectInterface 
// or SpyClonerInterface
{}
```

You can create a custom Cloner to copy your object :
-   Create the Cloner that should implements `Eniams\Spy\Cloner\ClonerInterface`.
-   Create an interface related to the created Cloner that should implements `Eniams\Spy\SpyInterface`.

```php
<?php
namespace App\Service;
// Create the Cloner
class UserLandCloner implements \Eniams\Spy\Cloner\ClonerInterface
{
    public function doClone($object)
    {
        // Stuff to clone the given $object.
    }
    
    public function supports($object): bool
    {
        return $object instanceof UserLandClonerInterface;
    }   
}

// Create the Interface
interface UserLandClonerInterface extends \Eniams\Spy\SpyInterface

// Use your Cloner (Implement the created interface in the class) 
class Foo implements \Eniams\Spy\Cloner\UserLandClonerInterface

```

2. Register the Cloners in the `Eniams\Spy\ClonerChainCloner`

```php
<?php
 $chainCloner = new \Eniams\Spy\Cloner\ChainCloner([new DeepCopyCloner(), new SpyCloner(), new UserLandCloner()]);
```

3. Time to spy your object :shipit: 

```php
<?php
$spied = new \Eniams\Spy\Spy($foo, $chainCloner);

$spied->isModified();
$spied->isNotModified();
```

##### Now, you want to want to know if a specific property was modified and get the initial and the current value.
```php
<?php
$foo = (new \App\Entity\Foo())->setName('Smaone');

$spied = new \Eniams\Spy\Spy($foo, $chainCloner);

$foo->setName('Dude');

$spied->isPropertyModified('name'); // output true

$propertyState = $spied->getPropertState('name');

$propertyState->getFqcn(); // App\Entity\Foo
$propertyState->getProperty(); // 'name'
$propertyState->getInitialValue(); // 'Smaone'
$propertyState->getCurrentValue(); // 'Dude'
```

##### Working with Services container you can store an object to retrieve later in your application 

```php
<?php
$object = (new \Foo\Entity\User())->setName('Smaone');
$spyBase = (new \Eniams\Spy\SpyBase());
$spyBase->add('your_key', $object); // behind the scene $object is converted to a \Eniams\Spy\Spy object and the cloner class will be resolve by the interface implemented by the $object.

$yourContainer
    ->register('spy_base_service', $spyBase);

$spyBase = $yourContainer->get('spy_base_service');
// fetch the object
$spyBase->get('your_key');

// remove
$spyBase->remove('your_key');
```

##### For simple use case that don't need to clone an object, you can also check the difference between 2 "same" classes.
```php
$firstUser = (new App\Entity\User())->setName('Smaone');
$secondUser = (new App\Entity\User())->setName('Dude');

$propertyState = Eniams\Spy\Property\PropertyStateFactory::createPropertyState('name', $firstUser, $secondUser);

$propertyState->getFqcn(); // App\Entity\User
$propertyState->getProperty(); // 'name'
$propertyState->getInitialValue(); // 'Smaone'
$propertyState->getCurrentValue(); // 'Dude'
``` 

## Symfony Integration
