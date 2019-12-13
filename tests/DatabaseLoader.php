<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\BlameableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\FilterableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\GeocodableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\LoggableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\SluggableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\SluggableMultiEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\SoftDeletableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\TimestampableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\Translatable\ExtendedTranslatableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\Translatable\ExtendedTranslatableEntityTranslation;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\TranslatableEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\TranslatableEntityTranslation;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\TreeNodeEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\UserEntity;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\UuidableEntity;

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
            UuidableEntity::class,
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
