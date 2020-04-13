# Spy

Spy helps you to know if an object was modified and allow you to fire event when the given object is modified.  

⚠️ *This project is a work in progress.* ⚠️

## How to spy an object ?

##### Suppose you want to spy a `foo` object to know if it was modified :

```php
<?php
$spied = new \Eniams\Spy\Spy($foo);

$spied->isModified();
$spied->isNotModified();
```

##### Now, you want to want to know if a specific property was modified and get the initial and the current value.
```php
<?php
$object = (new \Foo\Entity\User())->setName('Smaone');

$spied = new \Eniams\Spy\Spy($object);

$object->setName('Dude');

$spied->isPropertyModified('name'); // output true

$propertyState = $spied->getPropertState('name');

$propertyState->getFqcn(); // Foo\Entity\User
$propertyState->getProperty(); // 'name'
$propertyState->getInitialValue(); // 'Smaone'
$propertyState->getCurrentValue(); // 'Dude'
```

##### Working with Services container you can store an object to retrieve later in your application 

```php
<?php
$object = (new \Foo\Entity\User())->setName('Smaone');
$spyBase = (new \Eniams\Spy\SpyBase());
$spyBase->add('your_key', $object); // behind the scene $object is converted to a \Eniams\Spy\Spy object

$yourContainer
    ->register('spy_base_service', $spyBase);

$spyBase = $yourContainer->get('spy_base_service');
// fetch the object
$spyBase->get('your_key');

// remove
$spyBase->remove('your_key');
```

##### You can also check the difference between 2 "same" objects.
```php
$firstUser = (new \Foo\Entity\User())->setName('Smaone');
$secondUser = (new \Foo\Entity\User())->setName('Dude');

$propertyState = Eniams\Spy\Property\PropertyStateFactory::createPropertyState('name', $firstUser, $secondUser);

$propertyState->getFqcn(); // Foo\Entity\User
$propertyState->getProperty(); // 'name'
$propertyState->getInitialValue(); // 'Smaone'
$propertyState->getCurrentValue(); // 'Dude'
``` 
