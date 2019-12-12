# Blameable

Blameable is able to **track entity creators and updaters**.

- `Knp\DoctrineBehaviors\Contract\Provider\UserProviderInterface`

A Blameable [callable](#callables) is used to get the current user from your application.

In the case you are using a Doctrine Entity to represent your users, you can configure the subscriber
to manage automatically the association between this user entity and your entites.

Using Symfony, all you have to do is to configure the DI parameter named `%knp.doctrine_behaviors.blameable_subscriber.user_entity%` with a fully qualified namespace,
for example:

## Entity

```php
<?php

declare(strict_types=1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\BlameableInterface;
use Knp\DoctrineBehaviors\Model\Blameable\Blameable;

/**
 * @ORM\Entity
 */
class Category implements BlameableInterface
{
    use Blameable;
}
```

## Usage

Then, you can use it like that:

```php
<?php

$category = new Category;
$entityManager->persist($category);

$creator = $category->getCreatedBy();
$updater = $category->getUpdatedBy();
```