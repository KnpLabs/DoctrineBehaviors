# Soft-Deletable

## Entity

```php 
<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\SoftDeletableInterface;
use Knp\DoctrineBehaviors\Model\SoftDeletable\SoftDeletable;

/**
 * @ORM\Entity
 */
class Category implements SoftDeletableInterface
{
    use SoftDeletable;
}
```

## Usage

```php
<?php

$category = new Category();
$entityManager->persist($category);
$entityManager->flush();

// get id
$id = $category->getId();

// now remove it
$entityManager->remove($category);
$entityManager->flush();

// hey, I'm still here:
$categoryRepository = $entityManager->getRepository(Category::class);
$category = $categoryRepository->findOneById($id);

// but I'm "deleted"
$category->isDeleted(); // === true

// restore me
$category->restore();

//look ma, I am back
$category->isDeleted(); // === false

// do not forget to call flush method to apply the change
$entityManager->flush();
```

```php
<?php

$category = new Category;

$entityManager->persist($category);
$entityManager->flush();

// I'll delete you tomorrow
$category->setDeletedAt((new \DateTime())->modify('+1 day'));

// OK, I'm here
$category->isDeleted(); // === false

/*
 *  24 hours later...
 */

// OK, I'm deleted
$category->isDeleted(); // === true
```
