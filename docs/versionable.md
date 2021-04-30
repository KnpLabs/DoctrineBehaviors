# Versionable

Do you need to version single text/number property? This is behavior for you.

Inspired from:

- https://github.com/beberlei/DoctrineExtensions/commit/258fa3abeb1a907e827fce7e208fc04376712085
- https://stackoverflow.com/questions/31563722/track-field-changes-on-doctrine-entity
- https://stackoverflow.com/questions/28561434/doctrine-2-version-doesnt-work/28663975

@todo

`Versionable` allows you to tag your entities by the `DoctrineExtensions\Versionable\Versionable`
interface, which leads to snapshots of the entities being made upon saving to the database.

The interface `Versionable` is modified considerably by removing all the `getResourceId()`, `getVersionedData()` and
`getCurrentVersion()` methods, since Doctrine can easily retrieve these values on its own using the UnitOfWork API.

`Versionable` is then just a marker interface.

### What Versionable does?

Whenever an entity that implements Versionable is *updated* all the old values of the entity are saved with their old version number into a newly created `ResourceVersion` entity.

### Requirements of your entities are:

* Single Identifier Column (String or Integer)
* Entity has to be versioned (using @version annotation)

Implementing `Versionable` would look like:

```php
    namespace MyProject;
    use DoctrineExtensions\Versionable\Versionable;

    class BlogPost implements Versionable
    {
        // blog post API
    }
```

### Configuration

You have to add the `DoctrineExtensions\Versionable\Entity\ResourceVersion` entity to your metadata paths.
It is using the Annotation Metadata driver, so you have to specifiy or configure the path to the directory on the CLI.
Also if you are using any other metadata driver you have to wrap the `Doctrine\ORM\Mapping\Driver\DriverChain`
to allow for multiple metadata drivers.

You also have to hook the `VersionListener` into the EntityManager's EventManager explicitly upon
construction:

```php
    $eventManager = new EventManager();
    $eventManager->addEventSubscriber(new VersionListener());
    $em = EntityManager::create($connOptions, $config, $eventManager);
```

Using the `VersionManager` you can now retrieve all the versions of a versionable entity:

    $versionManager = new VersionManager($em);
    $versions = $versionManager->getVersions($blogPost);

Or you can revert to a specific version number:

```php
    $versionManager = new VersionManager($em);
    $versionManager->revert($blogPost, 100);
    $em->flush();
```