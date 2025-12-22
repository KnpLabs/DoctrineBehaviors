<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;

final class DatabaseLoader
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        Connection $connection
    ) {
        // @see https://stackoverflow.com/a/35222045/1348344
        $configuration = $connection->getConfiguration();
        if (method_exists($configuration, 'setSQLLogger')) {
            $configuration->setSQLLogger();
        }
    }

    public function reload(): void
    {
        $classMetadataFactory = $this->entityManager->getMetadataFactory();

        $classesMetadatas = $classMetadataFactory->getAllMetadata();

        $entityClasses = [];
        foreach ($classesMetadatas as $classMetadata) {
            $entityClasses[] = $classMetadata->getName();
        }

        $this->reloadEntityClasses($entityClasses);
    }

    /**
     * @param class-string[] $entityClasses
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
