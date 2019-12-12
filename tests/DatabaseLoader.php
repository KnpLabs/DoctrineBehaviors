<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\BlameableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\FilterableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\GeocodableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\LoggableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\SluggableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\SluggableMultiEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\SoftDeletableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\TimestampableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\Translatable\ExtendedTranslatableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\Translatable\ExtendedTranslatableEntityTranslation;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\TranslatableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\TranslatableEntityTranslation;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\TreeNodeEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\ORM\UserEntity;

final class DatabaseLoader
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager, Connection $connection)
    {
        $this->entityManager = $entityManager;

        // @see https://stackoverflow.com/a/35222045/1348344
        $connection->getConfiguration()->setSQLLogger(null);
    }

    public function reload(): void
    {
        $entityClasses = [
            SluggableEntity::class,
            FilterableEntity::class,
            GeocodableEntity::class,
            LoggableEntity::class,
            SluggableMultiEntity::class,
            SoftDeletableEntity::class,
            TimestampableEntity::class,
            TranslatableEntity::class,
            TranslatableEntityTranslation::class,
            BlameableEntity::class,
            UserEntity::class,
            TreeNodeEntity::class,
            // translatable
            ExtendedTranslatableEntity::class,
            ExtendedTranslatableEntityTranslation::class,
        ];

        $this->reloadEntityClasses($entityClasses);
    }

    /**
     * @param string[] $entityClasses
     */
    public function reloadEntityClasses(array $entityClasses): void
    {
        $schema = [];
        foreach ($entityClasses as $entityClass) {
            $schema[] = $this->entityManager->getClassMetadata($entityClass);
        }

        $schemaTool = new SchemaTool($this->entityManager);
        $schemaTool->dropSchema($schema);
        $schemaTool->createSchema($schema);
    }
}
