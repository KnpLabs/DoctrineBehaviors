# Timestampable

## Entity

```php 
<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\Timestampable;

/**
 * @ORM\Entity
 */
class Category implements TimestampableInterface
{
    use Timestampable;
}
```

### Usage

```php
<?php

$category = new Category;
$entityManager->persist($category);
$entityManager->flush();

$id = $category->getId();

$categoryRepository = $entityManager->getRepository(Category::class);

$category = $categoryRepository->findOneById($id);

$category->getCreatedAt();
$category->getUpdatedAt();
```

## Configuration

If you wish to change the doctrine type of the database fields that will be created for timestampable models you can
set the following parameter like so:

```yaml
parameters:
    knp.doctrine_behaviors.timestampable_subscriber.db_field_type: datetimetz
```

`datetimetz` here is a useful one to use if you are working with a Postgres database, otherwise you may encounter some
timezone issues. For more information on this see [Dbal documentation](http://doctrine-dbal.readthedocs.org/en/latest/reference/known-vendor-issues.html#datetime-datetimetz).

The default type is `datetime`.
