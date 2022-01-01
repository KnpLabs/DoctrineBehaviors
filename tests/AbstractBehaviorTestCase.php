<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\DBAL\Platforms\PostgreSQL94Platform;
use Doctrine\ORM\EntityManagerInterface;
use Knp\DoctrineBehaviors\Exception\ShouldNotHappenException;
use Knp\DoctrineBehaviors\Tests\HttpKernel\DoctrineBehaviorsKernel;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class AbstractBehaviorTestCase extends TestCase
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    private static ContainerInterface|null $container = null;

    protected function setUp(): void
    {
        if (static::$container === null) {
            $customConfig = $this->provideCustomConfig();
            if ($customConfig === null) {
                $customConfigs = [];
            } else {
                $customConfigs = [$customConfig];
            }

            $doctrineBehaviorsKernel = new DoctrineBehaviorsKernel($customConfigs);
            $doctrineBehaviorsKernel->boot();

            static::$container = $doctrineBehaviorsKernel->getContainer();
        }

        $this->entityManager = $this->getService('doctrine.orm.entity_manager');
        $this->loadDatabaseFixtures();
    }

    protected function loadDatabaseFixtures(): void
    {
        /** @var DatabaseLoader $databaseLoader */
        $databaseLoader = $this->getService(DatabaseLoader::class);
        $databaseLoader->reload();
    }

    protected function isPostgreSql(): bool
    {
        /** @var Connection $connection */
        $connection = $this->entityManager->getConnection();

        return $connection->getDatabasePlatform() instanceof PostgreSQL94Platform;
    }

    protected function provideCustomConfig(): ?string
    {
        return null;
    }

    protected function createAndRegisterDebugStack(): DebugStack
    {
        $debugStack = new DebugStack();

        $this->entityManager->getConnection()
            ->getConfiguration()
            ->setSQLLogger($debugStack);

        return $debugStack;
    }

    /**
     * @template T as object
     * @param class-string<T> $type
     * @return T
     */
    protected function getService(string $type): object
    {
        if (static::$container === null) {
            throw new ShouldNotHappenException();
        }

        return static::$container->get($type);
    }
}
