# Doctrine2 Behaviors

[![Build Status](https://secure.travis-ci.org/KnpLabs/DoctrineBehaviors.png)](http://travis-ci.org/KnpLabs/DoctrineBehaviors)


This php 5.4+ library is a collection of traits
that add behaviors to Doctrine2 entites and repositories.

It currently handles:

 * [tree](#tree)
 * [translatable](#translatable)
 * [timestampable](#timestampable)
 * [softDeletable](#softDeletable)
 * [blameable](#blameable)
 * [loggable](#loggable)
 * [geocodable](#geocodable)
 * [filterable](#filterable)
 * [sluggable](#sluggable)
 * [taggable](#taggable)

## Notice:

Some behaviors (translatable, timestampable, softDeletable, blameable, geocodable) need Doctrine subscribers in order to work.
Make sure to activate them by reading the [Subscribers](#subscribers) section.


<a name="subscribers" id="subscribers"></a>
## Subscribers

If you use symfony2, you can easily register them by importing a service definition file:

``` yaml

    # app/config/config.yml
    imports:
        - { resource: ../../vendor/knplabs/doctrine-behaviors/config/orm-services.yml }

```

You can also register them using doctrine2 api:


``` php

<?php

$em->getEventManager()->addEventSubscriber(new \Knp\DoctrineBehaviors\ORM\Translatable\TranslatableSubscriber);
// register more if needed

```


## Usage

All you have to do is to define a Doctrine2 entity and use traits:

``` php

<?php

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

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
        ORMBehaviors\Geocodable\Geocodable,
        ORMBehaviors\Loggable\Loggable,
        ORMBehaviors\Sluggable\Sluggable
    ;

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

<a name="tree" id="tree"></a>
### tree:

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
    $root->getChildNodes(); // ArrayCollection
    $root[0][1]; // node or null
    $root->isLeaf(); // boolean
    $root->isRoot(); // boolean

```

<a name="translatable" id="translatable"></a>
### translatable:

If you're working on a `Category` entity, the `Translatable` behavior expects a
**CategoryTranslation** entity.  This naming convention avoids you to handle manually entity associations.
It is handled automatically by the TranslationSubscriber.

In order to use the Translatable trait, you will have to create this `CategoryTranslation` entity.


``` php

<?php

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * @ORM\Entity
 */
class CategoryTranslation
{
    use ORMBehaviors\Translatable\Translation;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $description;

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

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param  string
     * @return null
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }
}
```
The corresponding Category entity needs to `use ORMBehaviors\Translatable\Translatable;`
and should only contain fields that you do not need to translate.

```
<?php
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * @ORM\Entity
 */
class Category
{
    use ORMBehaviors\Translatable\Translatable;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $someFieldYouDoNotNeedToTranslate;
}
```


After updating the database, ie. with `./console doctrine:schema:update --force`, you can now work on translations using `translate` or `getTranslations` methods.

``` php

<?php

    $category = new Category;
    $category->translate('fr')->setName('Chaussures');
    $category->translate('en')->setName('Shoes');
    $em->persist($category);

    // In order to persist new translations, call mergeNewTranslations method, before flush
    $category->mergeNewTranslations();

    $category->translate('en')->getName();

```

#### guess the current locale

You can configure the way the subscriber guesses the current locale, by giving a callable as its first argument.
This library provides a callable object (`Knp\DoctrineBehaviors\ORM\Translatable\CurrentLocaleCallable`) that returns the current locale using Symfony2.


#### proxy translations

An extra feature allows you to proxy translated fields of a translatable entity.

You can use it in the magic `__call` method of you translatable entity
so that when you try to call `getName` (for example) it will return you the translated value of the name for current locale:

``` php

<?php

    public function __call($method, $arguments)
    {
        return $this->proxyCurrentLocaleTranslation($method, $arguments);
    }

```


<a name="softDeletable" id="softDeletable"></a>
### soft-deletable

``` php

<?php

    $category = new Category;
    $em->persist($category);
    $em->flush();

    // get id
    $id = $category->getId();

    // now remove it
    $em->remove($category);
    $em->flush();

    // hey, i'm still here:
    $category = $em->getRepository('Category')->findOneById($id);

    // but i'm "deleted"
    $category->isDeleted(); // === true
```

``` php

<?php

    $category = new Category;
    $em->persist($category);
    $em->flush();

    // I'll delete you tomorow
    $category->setDeletedAt((new \DateTime())->modify('+1 day'));

    // Ok, I'm here
    $category->isDeleted(); // === false

    /*
     *  24 hours later...
     */

    // Ok I'm deleted
    $category->isDeleted(); // === true
```

<a name="timestampable" id="timestampable"></a>
### timestampable

``` php

<?php

    $category = new Category;
    $em->persist($category);
    $em->flush();

    $id = $category->getId();
    $category = $em->getRepository('Category')->findOneById($id);

    $category->getCreatedAt();
    $category->getUpdatedAt();

```


<a name="blameable" id="blameable"></a>
### blameable

Blameable is able to track creators and updators of a given entity.
A blameable [callable](#callables) is used to get the current user from your application.

In the case you are using a Doctrine Entity to represent your users, you can configure the subscriber
to manage automatically the association between this user entity and your entites.

Using symfony2, all you have to do is to configure the DI parameter named `%knp.doctrine_behaviors.blameable_subscriber.user_entity%` with a fully qualified namespace,
for example:

    # app/config/config.yml

    parameters:
        knp.doctrine_behaviors.blameable_subscriber.user_entity: AppBundle\Entity\User

Then, you can use it like that:

``` php

<?php

    $category = new Category;
    $em->persist($category);

    // instances of %knp.doctrine_behaviors.blameable_subscriber.user_entity%
    $creator = $em->getCreatedBy();
    $updater = $em->getUpdatedBy();

```

<a name="loggable" id="loggable"></a>
### loggable

Loggable is able to track lifecycle modifications and log them using any third party log system.
A loggable [callable](#callables) is used to get the logger from anywhere you want.

``` php

<?php

/**
 * @ORM\Entity
 */
class Category
{
    use ORMBehaviors\Loggable\Loggable;

    // you can override the default log messages defined in trait:
    public function getUpdateLogMessage(array $changeSets = [])
    {
        return 'Changed: '.print_r($changeSets, true);
    }

    public function getRemoveLogMessage()
    {
        return 'removed!';
    }
}

```

These messages are then passed to the configured callable.
You can define your own, by passing another callable to the LoggableSubscriber:


``` php

<?php

$em->getEventManager()->addEventSubscriber(
    new \Knp\DoctrineBehaviors\ORM\Loggable\LoggableSubscriber(
        new ClassAnalyzer,
        function($message) {
            // do stuff with message
        }
    )
);


```

If you're using symfony, you can also configure which callable to use:

    // app/config/config.yml
    parameters:
        knp.doctrine_behaviors.loggable_subscriber.logger_callable.class: Your\InvokableClass


<a name="geocodable" id="geocodable"></a>
### geocodable

Geocodable Provides extensions to PostgreSQL platform in order to work with cube and earthdistance extensions.

It allows you to query entities based on geographical coordinates.
It also provides an easy entry point to use 3rd party libraries like the exellent [geocoder](https://github.com/willdurand/Geocoder) to transform addresses into latitude and longitude.


``` php

<?php

    $geocoder = new \Geocoder\Geocoder;
    // register geocoder providers

    // $subscriber instanceof GeocodableSubscriber (add "knp.doctrine_behaviors.geocodable_subscriber" into your services.yml)
    $subscriber->setGeolocationCallable(function($entity) use($geocoder) {
        $location = $geocoder->geocode($entity->getAddress());
        return new Point(
            $location->getLatitude(),
            $location->getLongitude()
        ));
    });

    $category = new Category;
    $em->persist($category);

    $location = $category->getLocation(); // instanceof Point

    // find cities in a cricle of 500 km around point 47 lon., 7 lat.
    $nearCities = $repository->findByDistance(new Point(47, 7), 500);

```

<a name="sluggable" id="sluggable"></a>
### sluggable

Sluggable generates slugs (uniqueness is not guaranteed) for an entity.
Will automatically generate on update/persist (you can disable the on update generation by overriding `getRegenerateSlugOnUpdate` to return false.
You can also override the slug delimiter from the default hyphen by overriding `getSlugDelimiter`.
Use cases include SEO (i.e. URLs like http://mysite.com/post/3/introduction-to-php)
```php
<?php

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * @ORM\Entity
 */
class BlogPost
{
    use ORMBehaviors\Sluggable\Sluggable;

    /**
     * @ORM\Column(type="string")
     */
    protected $title;

    public function getSluggableFields()
    {
        return [ 'title' ];
    }
}
```

<a name="filterable" id="filterable"></a>
### filterable:

Filterable can be used at the Repository level

It allows to simple filter our result

Joined filters example:

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

and repository:

```php
<?php

use Knp\DoctrineBehaviors\ORM\Filterable;
use Doctrine\ORM\EntityRepository;

class ProductRepository extends EntityRepository
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
        return $this
            ->createQueryBuilder('e')
            ->leftJoin('e.orders', 'o');
    }
}
```

Now we can filtering using:

```php
    $products = $em->getRepository('Product')->filterBy(['o:code' => '21']);
```



<a name="callables" id="callables"></a>
## callables

Callables are used by some subscribers like blameable and geocodable to fill information based on 3rd party system.

For example, the blameable callable can be any symfony2 service that implements  `__invoke` method or any anonymous function, as soon as they return currently logged in user representation (which means everything, a User entity, a string, a username, ...).
For an example of DI service that is invoked, look at the `Knp\DoctrineBehaviors\ORM\Blameable\UserCallable` class.

In the case of geocodable, you can set it as any service that implements `__invoke` or anonymous function that returns a `Knp\DoctrineBehaviors\ORM\Geocodable\Type\Point` object.

<a name="taggable" id="taggable"></a>
### taggable

Taggable behaviour allows to add tags on your doctrine entities easily 

```php
<?php

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * @ORM\Entity
 */
class BlogPost
{
    use ORMBehaviors\Taggable\Taggable;

    /**
     * @ORM\Column(type="string")
     */
    protected $title;
}
```

If you want to override getters/setters you need to implement method(s) from TaggableInterface
```
\Knp\DoctrineBehaviors\Model\Taggable\TaggableInterface
```

```php
<?php

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * @ORM\Entity
 */
class BlogPost implements ORMBehaviors\Taggable\Taggable
{
    use ORMBehaviors\Taggable\Taggable;

    /**
     * @ORM\Column(type="string")
     */
    protected $title;
    
    /**
     * @param string $tags
     */
    public function removeTags($tags)
    {
        $tags = $this->validateTags($tags);
        $oldTags = $this->getTags()->filter(function($entry) use ($tags) {
            return in_array($entry->getName(), $tags);
        });
        foreach($oldTags as $tag) {
            $this->getTags()->removeElement($tag);
        }
    }
}
```

<a name="taggable" id="taggable"></a>
### taggable

Taggable behaviour allows to add tags on your doctrine entities easily

```php
<?php

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * @ORM\Entity
 */
class BlogPost
{
    use ORMBehaviors\Taggable\Taggable;

    /**
     * @ORM\Column(type="string")
     */
    protected $title;
}
```

If you want to override getters/setters you need to implement method(s) from TaggableInterface
```
\Knp\DoctrineBehaviors\Model\Taggable\TaggableInterface
```

```php
<?php

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * @ORM\Entity
 */
class BlogPost implements ORMBehaviors\Taggable\Taggable
{
    use ORMBehaviors\Taggable\Taggable;

    /**
     * @ORM\Column(type="string")
     */
    protected $title;

    /**
     * @param string $tags
     */
    public function removeTags($tags)
    {
        $tags = $this->validateTags($tags);
        $oldTags = $this->getTags()->filter(function($entry) use ($tags) {
            return in_array($entry->getName(), $tags);
        });
        foreach($oldTags as $tag) {
            $this->getTags()->removeElement($tag);
        }
    }
}
```

