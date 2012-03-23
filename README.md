# Doctrine2 Behaviors

This php 5.4+ library is a collection of traits 
that add behaviors to Doctrine2 entites and repositories.

It currently handles:

 * tree
 * translatable
 * timestampable
 * softDeletable
 * blameable

## Notice:

Some behaviors (translatable, timestampable, softDeletable, blameable) need Doctrine listeners in order to work.
Make sure to activate them by reading the **Listeners** section.  
Timestampable is a bit special in the sense it's only used to avoid having you to declare `hasLifecycleCallbacks` metadata.

Traits are based on annotation driver.  
You need to declare `use Doctrine\ORM\Mapping as ORM;` on top of your entity.


## Usage

All you have to do is to define a Doctrine2 entity and use traits:

``` php

<?php

use Doctrine\ORM\Mapping as ORM;

use Knp\DoctrineBehaviors\ORM as ORMBehaviors;

/**
 * @ORM\Entity(repositoryClass="CategoryRepository")
 */
class Category implements ORMBehaviors\Tree\NodeInterface, \ArrayAccess
{
    use ORMBehaviors\Tree\Node,
        ORMBehaviors\Translatable\Translatable,
        ORMBehaviors\Timestampable\Timestampable,
        ORMBehaviors\SoftDeletable\SoftDeletable,
        ORMBehaviors\Blameable\Blameable,

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    protected $id;
}

```


For some behaviors like tree, you can use repository traits:

``` php

<?php

use Doctrine\ORM\EntityRepository;
use Knp\DoctrineBehaviors\ORM as ORMBehaviors;

class CategoryRepository extends EntityRepository
{
    use ORMBehaviors\Tree\Tree,
}

```

Voila!

You now have a working `Category` that behaves like:

### tree node:

``` php

<?php

    $category = new Category;
    $category->setId(1); // tree nodes need an id to construct path.
    $child = new Category;
    $child->setId(2);

    $child->setChildOf($category);

    $em->persist($child);
    $em->persist($category);
    $em->flush();

    $root = $em->getRepository('Category')->getTree();

    $root->getParent(); // null
    $root->getNodeChildren(); // collection
    $root[0][1]; // node or null
    $root->isLeaf(); // boolean
    $root->isRoot(); // boolean

```

### translatable:

Translatable behavior waits for a Category**Translation** entity.  
This naming convention avoids you to handle manually entity associations. It is handled automatically by the TranslationListener.

In order to use Translatable trait, you will have to create this entity.


``` php

<?php

use Doctrine\ORM\Mapping as ORM;

use Knp\DoctrineBehaviors as DoctrineBehaviors;

/**
 * @ORM\Entity
 */
class CategoryTranslation
{
    use DoctrineBehaviors\Translatable\Translation;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param  string
     * @return null
     */
    public function setName($name)
    {
        $this->name = $name;
    }
}

```

Now you can work on translations using `translate` or `getTranslations` methods.

``` php

<?php

    $category = new Category;
    $category->translate('fr')->setName('Chaussures');
    $category->translate('en')->setName('Shoes');
    $em->persist($category);

    $category->translate('en')->getName();

```

### soft-deletable

``` php

<?php

    $category = new Category;
    $em->persist($category);
    $em->flush();

    // get id
    $id = $em->getId();

    // now remove it
    $em->remove($category);

    // hey, i'm still here:
    $category = $em->getRepository('Category')->findOneById($id);

    // but i'm "deleted"
    $category->isDeleted(); // === true
```

### blameable

``` php

<?php

    $category = new Category;
    $em->persist($category);

    // instances of UserInterface if configured with symfony2
    $creator = $em->getCreatedBy();
    $updater = $em->getUpdatedBy();

```

## Listeners

If you use symfony2, you can easilly register them by importing a service definition file:

``` yaml

    # app/config/config.yml
    imports:
        - { resource: ../../vendor/knp-doctrine-behaviors/config/orm-services.yml }

```

You can also register them using doctrine2 api:


``` php

<?php

$em->getEventManager()->addEventSubscriber(new \Knp\DoctrineBehaviors\ORM\Translatable\TranslatableListener);
// register more if needed

```

