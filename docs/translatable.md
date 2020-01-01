# Translatable

If you're working on a `<X>` entity, translatable behavior expects a `<X>Translation` entity in the
same folder, e.g.:

- `app/Entity/Category.php`
- `app/Entity/CategoryTranslation.php`

The default naming convention (or its customization via trait methods) avoids you to manually handle entity associations. It is handled automatically by the `TranslationSubscriber`.

## Entity

```php
<?php

declare(strict_types=1);    

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait;

/**
 * @ORM\Entity
 */
class CategoryTranslation implements TranslationInterface
{
    use TranslationTrait;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $description;

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }
}
```

The corresponding Category entity needs to `use Translatable;`
and should only contain fields that you do not need to translate.

```php
<?php

declare(strict_types=1);    

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;

/**
 * @ORM\Entity
 */
class Category implements TranslatableInterface
{
    use TranslatableTrait;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $someFieldYouDoNotNeedToTranslate;
}
```


After updating the database, ie. with `bin/console doctrine:schema:update --force`,
you can now work on translations using `translate()` or `getTranslations()` methods.

```php
<?php

declare(strict_types=1);

/** @var \Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface $category */
$category = new Category();
$category->translate('fr')->setName('Chaussures');
$category->translate('en')->setName('Shoes');

$entityManager->persist($category);

// In order to persist new translations, call mergeNewTranslations method, before flush
$category->mergeNewTranslations();

$category->translate('en')->getName();
```

#### Override

In case you prefer to use a different class name for the translation entity, or want to use a separate namespace, you have 2 ways:

If you want to define a custom translation entity class name globally: Override the trait `Translatable` and his  method `getTranslationEntityClass` and the trait `Translation` and his method `getTranslatableEntityClass` in the translation entity.

If you override one, you also need to override the other to return the inverse class.

Example: Let's say you want to create a sub namespace AppBundle\Entity\Translation to stock translations classes
then put overrided traits in that folder.

```php
<?php

declare(strict_types=1);

namespace AppBundle\Behavior;

use Knp\DoctrineBehaviors\Model\Translatable\TranslatableTrait;

trait TranslatableTrait
{
    use TranslatableTrait;

    public static function getTranslationEntityClass(): string
    {
        $explodedNamespace = explode('\\', __CLASS__);
        $entityClass = array_pop($explodedNamespace);
        
        return '\\' . implode('\\', $explodedNamespace) . '\\Translation\\' . $entityClass . 'Translation';
    }
}
```

```php
<?php

declare(strict_types=1);

namespace AppBundle\Behavior;

use Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait;

trait TranslationTrait
{
    use TranslationTrait;

    public static function getTranslatableEntityClass(): string
    {
        $explodedNamespace = explode('\\', __CLASS__);
        $entityClass = array_pop($explodedNamespace);
        // Remove Translation namespace
        array_pop($explodedNamespace);
        
        return '\\' . implode('\\', $explodedNamespace) . '\\' . substr($entityClass, 0, -11);
    }
}
```

If you want to define a custom translation entity class name just for a single translatable class :
Override the trait method `getTranslationEntityClass` in the translatable entity and `getTranslatableEntityClass`
in the translation entity. If you override one, you also need to override the other to return the inverse class.

#### Provide Current Locale

This library provides an interface `Knp\DoctrineBehaviors\Contract\Provider\LocaleProviderInterface` that [by default returns the current locale using Symfony `RequestStack` or `TranslatorInterface`](https://github.com/KnpLabs/DoctrineBehaviors/blob/master/src/Provider/LocaleProvider.php).

#### Proxy Translations

An extra feature allows you to proxy translated fields of a translatable entity.

You can use it in the magic `__call` method of you translatable entity
so that when you try to call `getName` (for example) it will return you the translated value of the name for current locale:

```php
<?php

class SomeClass
{
    public function __call($method, $arguments)
    {
        return $this->proxyCurrentLocaleTranslation($method, $arguments);
    }

    // or do it with PropertyAccessor that ships with Symfony SE
    // if your methods don't take any required arguments
    public function __call($method, $arguments)
    {
        return \Symfony\Component\PropertyAccess\PropertyAccess::createPropertyAccessor()->getValue($this->translate(), $method);
    }
}
```

## Configuration

By default, translation relations are lazy loaded. To change that, just modify parameters in your config:

```yaml
# services.yaml
parameters:
    doctrine_behaviors_translatable_fetch_mode: "LAZY"
    doctrine_behaviors_translation_fetch_mode: "LAZY"
```
