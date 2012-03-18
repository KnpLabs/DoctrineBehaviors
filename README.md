# Doctrine2 Behaviors

This php 5.4+ library is a collection of traits 
that add behaviors to Doctrine2 entites and repositories.

It currently handles:

 * tree
 * sortable
 * translatable
 * timestampable
 * softDeletable

## Notice:

Some behaviors (translatable, timestampable, softDeletable) need Doctrine listeners in order to work.
Make sure to activate them by reading the **Listeners** section.  
Timestampable is a bit special in the sense it's only used to avoid having you to declare `hasLifecycleCallbacks` metadata.

Traits are based on annotation driver.  
You need to declare `use Doctrine\ORM\Mapping as ORM;` on top of your entity.


## Usage

All you have to do is to define a Doctrine2 entity and use traits:

``` php

use Doctrine\ORM\Mapping as ORM;

use Knp\DoctrineBehaviors as DoctrineBehaviors;

/**
 * @ORM\Entity(repositoryClass="CategoryRepository")
 */
class Category implements DoctrineBehaviors\Tree\NodeInterface, \ArrayAccess
{
    use DoctrineBehaviors\Tree\Node,
        DoctrineBehaviors\Translatable\Translatable,
        DoctrineBehaviors\Timestampable\Timestampable,
        DoctrineBehaviors\SoftDeletable\SoftDeletable,
        DoctrineBehaviors\Sortable\SortableEntity;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="NONE")
     */
    protected $id;
}

```


For some behaviors like sortable and tree, you can use repository traits:

``` php

use Doctrine\ORM\EntityRepository;
use Knp\DoctrineBehaviors\ORM as DoctrineBehaviors;

class CategoryRepository extends EntityRepository
{
    use DoctrineBehaviors\Tree\Tree,
        DoctrineBehaviors\Sortable\SortableRepository;
}

```

Voila!

You now have a working `Category` that behaves like:

 * tree node:

``` php

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

 * translatable:

Translatable behavior waits for a Category**Translation** entity.  
This naming convention avoids you to handle manually entity associations. It is handled automatically by the TranslationListener.

In order to use Translatable trait, you will have to create this entity.


``` php

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

    $category = new Category;
    $category->translate('fr')->setName('Chaussures');
    $category->translate('en')->setName('Shoes');
    $em->persist($category);

    $category->translate('en')->getName();

```

 * sortable:

``` php

    $category = new Category;
    $em->persist($category);

    $category2 = new Category;
    $em->persist($category2);
    $category2->setSort(2);

    $em->getRepository('Category')->reorderEntity($category2);

```


## Listeners
