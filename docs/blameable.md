# Blameable

Blameable is able to **track entity creators and updaters**.

## Entity

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\BlameableInterface;
use Knp\DoctrineBehaviors\Model\Blameable\BlameableTrait;

/**
 * @ORM\Entity
 */
class Category implements BlameableInterface
{
    use BlameableTrait;
}
```

## How it Works

By default, the current user from Symfony\Security is used.
If you want to change it, just implement `Knp\DoctrineBehaviors\Contract\Provider\UserProviderInterface` youself and override native service.

## Usage

Then, you can use it like that:

```php
<?php

/** @var Knp\DoctrineBehaviors\Contract\Entity\BlameableInterface $category */
$category = new Category();
$entityManager->persist($category);

$createdBy = $category->getCreatedBy();
var_dump($createdBy); 
// "App\Entity\User" object

$updatedBy = $category->getUpdatedBy();
var_dump($updatedBy);
// "App\Entity\User" object
```
