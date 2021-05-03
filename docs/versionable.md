# Versionable

Do you need to version single text/number property? This is behavior for you.

### What Versionable does?

Whenever an entity that implements `VersionableInterface` is *updated* all the old values of the entity are saved with their old version number into a newly created `ResourceVersion` entity.

### Requirements of your entities are:

* single `@ORM\ID` with string or integer type
* a `$version` property with `@ORM\Version` annotation (handled by `VersionableTrait)

```php
namespace App;
    
use Knp\DoctrineBehaviors\Versionable\Behavior\VersionableTrait;
use Knp\DoctrineBehaviors\Versionable\Contract\VersionableInterface;

class BlogPost implements VersionableInterface
{
    use VersionableTrait;

    // blog post API
}
```

### Configuration

You have to add the `DoctrineExtensions\Versionable\Entity\ResourceVersion` entity to your metadata paths.
It is using the Annotation Metadata driver, so you have to specifiy or configure the path to the directory on the CLI.
Also if you are using any other metadata driver you have to wrap the `Doctrine\ORM\Mapping\Driver\DriverChain`
to allow for multiple metadata drivers.

You also have to hook the `VersionListener` into the EntityManager's EventManager explicitly upon construction:

```php
$eventManager = new EventManager();
$eventManager->addEventSubscriber(new VersionListener());
$em = EntityManager::create($connOptions, $config, $eventManager);
```

Using the `VersionManager` you can now retrieve all the versions of a versionable entity:

```php
$versionManager = new VersionManager($em);
$versions = $versionManager->getVersions($blogPost);
```

Or you can revert to a specific version number:

```php
$versionManager = new VersionManager($em);
$versionManager->revert($blogPost, 100);
$em->flush();
```
