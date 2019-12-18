# Doctrine Behaviors

[![Build Status](https://img.shields.io/travis/KnpLabs/DoctrineBehaviors/master.svg?style=flat-square)](https://travis-ci.org/KnpLabs/DoctrineBehaviors)
[![Downloads](https://img.shields.io/packagist/dt/knplabs/doctrine-behaviors.svg?style=flat-square)](https://packagist.org/packages/knplabs/doctrine-behaviors)

This PHP library is a collection of traits and interfaces that add behaviors to Doctrine entities and repositories.

It currently handles:

 * [Blameable](/docs/blameable.md)
 * [Geocodable](/docs/geocodable.md)
 * [Loggable](/docs/loggable.md)
 * [Sluggable](/docs/sluggable.md)
 * [SoftDeletable](/docs/soft-deletable.md)
 * [Sortable](/docs/sortable.md)
 * [Uuidable](/docs/uuidable.md)
 * [Timestampable](/docs/timestampable.md)
 * [Translatable](/docs/translatable.md)
 * [Tree](/docs/tree.md)

## Install

```bash
composer require knplabs/doctrine-behaviors
```

Register bundle in `AppKernel`:

```php
<?php

declare(strict_types=1);

namespace App;

use Symfony\Component\HttpKernel\Kernel;

final class AppKernel extends Kernel
{
    public function registerBundles(): array
    {
        return [
            //...
            new Knp\DoctrineBehaviors\Bundle\DoctrineBehaviorsBundle(),
            //...
        ];
    }
}
```

## Usage

All you have to do is to define a Doctrine entity:
 
- implemented interface
- add a trait 

For some behaviors like tree, you can use repository traits:

```php
<?php

declare(strict_types=1);

namespace App\Repository;  

use Doctrine\ORM\EntityRepository;
use Knp\DoctrineBehaviors\ORM\Tree\TreeTrait;

final class CategoryRepository extends EntityRepository
{
    use TreeTrait;
}
```

Voilá!

You now have a working `Category` that behaves like:

## Testing

```bash
vendor/bin/phpunit
```

Run coding standard fixes and check static analysis:

```bash
composer fix-cs
composer phpstan
```
