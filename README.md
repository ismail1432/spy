# Spy

[![Build Status](https://travis-ci.com/ismail1432/spy.svg?branch=master)](https://travis-ci.org/ismail1432/spy)

Spy helps you to know if an object was modified and allow you to fire/listen an event when the given object is modified.  

⚠️ *This project is a work in progress.* ⚠️

### Installation

```ssh
$ composer require eniams/spy
```

### How it works ?

![Spy Workflow](/images/spy_workflow.jpg?raw=true)

![SpyBase Workflow](/images/spy_base_workflow.jpg?raw=true)

### Behind the scene:

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

If you're using Symfony thanks to the [autoconfigure tags](https://symfony.com/doc/current/service_container/tags.html) you don't have to follow the next step, the created cloner will be 
registered to the `ChainCloner` that is responsible to clone the oject to spy.
So you can go to step 3.
 
2. For Vanilla PHP if you don't want to use the default cloners you can Register yours in the `Eniams\Spy\ClonerChainCloner`

```php
<?php
 $chainCloner = new \Eniams\Spy\Cloner\ChainCloner([new UserLandCloner()]);
```

3. Time to spy your object :shipit: 

```php
<?php
// $chainCloner is optional and need to be use only if you want to use a custom cloners,
// for Symfony remember that your custom cloner is already registered in the `ChainCloner $chainCloner` and it is a public service that can be retrieve from the container.
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

$propertyState = $spied->getPropertyState('name');

$propertyState->getFqcn(); // App\Entity\Foo
$propertyState->getProperty(); // 'name'
$propertyState->getInitialValue(); // 'Smaone'
$propertyState->getCurrentValue(); // 'Dude'
```

##### Working with Services container you can store an object in the `SpyBase` to retrieve it later in your application 

##### Symfony
```php
<?php
class FooController extends AbstractController
{
    /**
     * @Route("/foo", name="foo")
     */
    public function index(SpyBase $spyBase)
    {
        $user = (new \Foo\Entity\User())->setName('Smaone');
        $spyBase = (new \Eniams\Spy\SpyBase());
        $spyBase->add('your_key', $user);
        
```
##### Vanilla PHP
```php
<?php
$user = (new \Foo\Entity\User())->setName('Smaone');
$spyBase = (new \Eniams\Spy\SpyBase());
$spyBase->add('your_key', $user); // behind the scene $object is converted to a \Eniams\Spy\Spy object and the cloner class will be resolve by the interface implemented by the $object.

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

##### More advanced use case

##### You can define a context to check some properties.
```php
class User implements SpyClonerInterface, PropertyCheckerContextInterface {

private $age;
private $adresse;
private $firstname;
 public static function propertiesInContext(): array
    {
        return [
            'context_check_firstname' => ['firstname', 'age'],
            'context_check_adresse' => ['adresse'],
        ];
    }
}

// index.php
$spied->isModifiedInContext(['context_check_firstname']); // true only if 'firstname', 'age' were modified
$spied->isModifiedInContext(['context_check_adresse']); // true only if 'adresse' is modified
$spied->getPropertiesModifiedInContext(['context_check_adresse']); // return modified properties for context context_check_adresse
```

##### You can define dynamically which properties to check
```php
class User implements SpyClonerInterface{

private $age;
private $adresse;
private $firstname;
}

// index.php
$spied->isModifiedForProperties(['age']); // true only if age was modified
``` 

##### You can exclude some properties.

```php
class User implements SpyClonerInterface, PropertyCheckerBlackListInterface {

private $age;
private $adresse;
private $firstname;

 public static function propertiesBlackList(): array
    {
        return ['age'];
    }
}
// index.php
$user->setAge(33);
$spied->isModified(); // return false because $age is blacklisted
$spied->getPropertiesModifiedWithoutBlackListContext(); // return age even it's blacklisted

```
