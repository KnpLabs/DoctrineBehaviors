<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\DBAL\Platforms\PostgreSQL94Platform;
use Doctrine\ORM\EntityManagerInterface;
use Knp\DoctrineBehaviors\Tests\HttpKernel\DoctrineBehaviorsKernel;
<<<<<<< HEAD
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
=======
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
>>>>>>> 7050548... fixup! misc

abstract class AbstractBehaviorTestCase extends AbstractKernelTestCase
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    protected function setUp(): void
    {
        $customConfig = $this->provideCustomConfig();
        if ($customConfig !== null) {
            self::bootKernelWithConfigs(DoctrineBehaviorsKernel::class, [$customConfig]);
        } else {
            self::bootKernel(DoctrineBehaviorsKernel::class);
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
}
