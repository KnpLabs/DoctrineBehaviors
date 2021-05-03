# Versionable

Do you need to version single text/number property? This is behavior for you.

### What Versionable does?

Whenever an entity that implements `VersionableInterface` is *updated* all the old values of the entity are saved with their old version number into a newly created `ResourceVersion` entity.

### Requirements of your entities are:

* single `@ORM\ID` with string or integer type
* a `$version` property with `@ORM\Version` annotation (handled by `VersionableTrait`)

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

Using the `VersionManager` you can now retrieve all the versions of a versionable entity:

<br>

**@todo consider using 1:m relation entity instead of `VersionManager` service middle man**

<br>

```php
use Doctrine\ORM\EntityManagerInterface;
use Knp\DoctrineBehaviors\Versionable\Tests\Entity\BlogPost;
use Knp\DoctrineBehaviors\Versionable\VersionManager;

final class SomeController
{
    public function __construct(
        private VersionManager $versionManager,
        private EntityManagerInterface $entityManager,
    ) {
    }
    
    public function run(BlogPost $blogPost)
    {
        // pick all versions of this entity    
        $versions = $this->versionManager->getVersions($blogPost);
    
        // you can revert to a specific version 
        $this->versionManager->revert($blogPost, 35);

        // don't forget to flush
        $this->entityManager->flush();
    }
}
```
