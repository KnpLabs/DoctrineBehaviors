# Tree

## Entity

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model\Tree\NodeInterface;
use Knp\DoctrineBehaviors\Model\Tree\Node;

/**
 * @ORM\Entity
 */
class Category implements NodeInterface
{
    use Node;
}
```

## Usage

```php
<?php

declare(strict_types=1);

$category = new Category;
$category->setId(1); // tree nodes need an id to construct path.
$child = new Category;
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