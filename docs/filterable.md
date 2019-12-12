# Filterable

Filterable can be used at the Repository level. It allows to simple filter our result.

## Entity

```php
<?php

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="ProductRepository")
 */
class ProductEntity
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="integer")
     */
    private $code;

    /**
     * @ORM\OneToMany(targetEntity="Order", mappedBy="product")
     */
    protected $orders;
}
```

## Repository

```php
<?php

use Knp\DoctrineBehaviors\ORM\Filterable;
use Doctrine\ORM\EntityRepository;

final class ProductRepository extends EntityRepository
{
    use Filterable\FilterableRepository;

    public function getLikeFilterColumns()
    {
        return ['e:name', 'o:code'];
    }

    public function getEqualFilterColumns()
    {
        return [];
    }

    protected function createFilterQueryBuilder()
    {
        return $this->createQueryBuilder('e')
            ->leftJoin('e.orders', 'o');
    }
}
```

Now we can filtering using:

```php
<?php

$productRepository = $entityManager->getRepository(Product::class);

$products = $productRepository->filterBy(['o:code' => '21']);
```
