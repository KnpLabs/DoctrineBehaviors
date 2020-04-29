# Timestampable
 
## Entity

```php 
<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface;
use Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;

/**
 * @ORM\Entity
 */
class Category implements TimestampableInterface
{
    use TimestampableTrait;
}
```

## Usage

```php
<?php

use App\Entity\Category;   

/** @var Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface $category */
$category = new Category();

$entityManager->persist($category);
$entityManager->flush();

var_dump($category->getCreatedAt());
// instanceof "DateTime"

var_dump($category->getUpdatedAt());
// instanceof "DateTime"
```
## Configuration

By default, timestampable use `datetime` field type. If you want use `datetimetz`, just modify parameters in your config:

```yaml
# knp_doctrine_behaviors.yaml
knp_doctrine_behaviors:
    # ...
    timestampable:
        date_field_type: "datetimetz"
```
