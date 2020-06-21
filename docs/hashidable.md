# Hashidable

To enable this behavior, make sure `RoukmouteHashidsBundle` is registered in your bundles:

```php
// bundles.php
return [
    Roukmoute\HashidsBundle\RoukmouteHashidsBundle::class => ['all' => true],
];
```

Hashidable generates hashIds for an entity. Will automatically generate on persist.

```php
<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Fixtures\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\HashidableInterface;
use Knp\DoctrineBehaviors\Model\Hashidable\HashidableTrait;

/**
 * @ORM\Entity
 */
class HashidableEntity implements HashidableInterface
{
    use HashidableTrait;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var int
     */
    private $id;

    public function getId(): int
    {
        return $this->id;
    }

    public function getHashidableField(): string
    {
        return 'id';
    }
}

```
