# Ulidable

Ulidable generates ulid for an entity. Will automatically generate on persist.

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\UlidableInterface;
use Knp\DoctrineBehaviors\Model\Ulidable\UlidableTrait;

/**
 * @ORM\Entity
 */
class BlogPost implements UlidableInterface
{
    use UlidableTrait;
}
```
