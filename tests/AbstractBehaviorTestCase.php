<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\ORM\EntityManagerInterface;
use Knp\DoctrineBehaviors\Tests\HttpKernel\DoctrineBehaviorsKernel;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

abstract class AbstractBehaviorTestCase extends AbstractKernelTestCase
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var DebugStack
     */
    protected $debugStack;

    protected function setUp(): void
    {
        $customConfig = $this->provideCustomConfig();
        if ($customConfig !== null) {
            self::bootKernelWithConfigs(DoctrineBehaviorsKernel::class, [$customConfig]);
        } else {
            self::bootKernel(DoctrineBehaviorsKernel::class);
        }

        $this->entityManager = static::$container->get('doctrine.orm.entity_manager');
        $this->loadDatabaseFixtures();
    }

    protected function loadDatabaseFixtures(): void
    {
        /** @var DatabaseLoader $databaseLoader */
        $databaseLoader = static::$container->get(DatabaseLoader::class);
        $databaseLoader->reload();
    }

    protected function isPostgreSql(): bool
    {
        /** @var Connection $connection */
        $connection = $this->entityManager->getConnection();

        return $connection->getDatabasePlatform() instanceof PostgreSqlPlatform;
    }

    protected function provideCustomConfig(): ?string
    {
        return null;
    }

    protected function enableDebugStackLogger(): void
    {
        $this->debugStack = new DebugStack();

        $this->entityManager->getConnection()
            ->getConfiguration()
            ->setSQLLogger($this->debugStack);
    }
}
