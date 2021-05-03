<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;

final class DatabaseLoader
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var SchemaToolFactory
     */
    private $schemaToolFactory;

    public function __construct(EntityManagerInterface $entityManager, Connection $connection)
    {
        $this->entityManager = $entityManager;
        $this->schemaToolFactory = new SchemaToolFactory($entityManager);

        // @see https://stackoverflow.com/a/35222045/1348344
        $configuration = $connection->getConfiguration();
        $configuration->setSQLLogger();
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
     * @param string[] $entityClasses
     */
    public function reloadEntityClasses(array $entityClasses): void
    {
        $schema = [];
        foreach ($entityClasses as $entityClass) {
            $schema[] = $this->entityManager->getClassMetadata($entityClass);
        }

        $schemaTool = $this->schemaToolFactory->create();
        $schemaTool->dropSchema($schema);
        $schemaTool->createSchema($schema);
    }
}
