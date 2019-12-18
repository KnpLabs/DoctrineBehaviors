# Sortable

@todo

## Entity

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\SortableInterface;
use Knp\DoctrineBehaviors\Model\Sortable\SortableTrait;

/**
 * @ORM\Entity
 */
class Category implements SortableInterface
{
    use SortableTrait;
}
```

## Usage

@todo

```php
$category = new Category;

$entityManager->persist($category);
$entityManager->flush();

$category->getSort(); // 1
```