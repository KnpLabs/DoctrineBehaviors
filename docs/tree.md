# Tree

## Entity

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TreeNodeInterface;
use Knp\DoctrineBehaviors\Model\Tree\TreeNodeTrait;

/**
 * @ORM\Entity
 */
class Category implements TreeNodeInterface
{
    use TreeNodeTrait;

    /**
     * @ORM\Column(type="string")
     * @var string
     */
    private $name;

    public function __toString() : string
    {
        return (string) $this->name;
    }
}
```

## Usage

```php
<?php

/** @var Knp\DoctrineBehaviors\Contract\Entity\TreeNodeInterface $category */
$category = new Category();
$category->setId(1);

$child = new Category();
$child->setId(2);

$child->setChildNodeOf($category);

$entityManager->persist($child);
$entityManager->persist($category);
$entityManager->flush();

$categoryRepository = $entityManager->getRepository(Category::class);

$root = $categoryRepository->getTree();

$root->getParentNode(); // null
$root->getChildNodes(); // ArrayCollection
$root[0][1]; // node or null
$root->isLeafNode(); // boolean
$root->isRootNode(); // boolean
```