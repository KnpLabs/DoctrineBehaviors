<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Logging\DebugStack;
use Doctrine\DBAL\Platforms\PostgreSQL94Platform;
use Doctrine\ORM\EntityManagerInterface;
use Knp\DoctrineBehaviors\Tests\HttpKernel\DoctrineBehaviorsKernel;
<<<<<<< HEAD
<<<<<<< HEAD
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
=======
<<<<<<< HEAD
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
=======
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
>>>>>>> 7050548... fixup! misc
>>>>>>> upgrade Kernel
=======
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
>>>>>>> misc

abstract class AbstractBehaviorTestCase extends TestCase
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

<<<<<<< HEAD
<<<<<<< HEAD
    private ContainerInterface $container;

    protected function setUp(): void
    {
        $doctrineBehaviorsKernel = new DoctrineBehaviorsKernel($this->provideCustomConfigs());
        $doctrineBehaviorsKernel->boot();

        $this->container = $doctrineBehaviorsKernel->getContainer();
=======
    private static ContainerInterface|null $container = null;
=======
    private ContainerInterface $container;
>>>>>>> fixing blameable test

    protected function setUp(): void
    {
        $doctrineBehaviorsKernel = new DoctrineBehaviorsKernel($this->provideCustomConfigs());
        $doctrineBehaviorsKernel->boot();

<<<<<<< HEAD
            $doctrineBehaviorsKernel = new DoctrineBehaviorsKernel($customConfigs);
            $doctrineBehaviorsKernel->boot();

            static::$container = $doctrineBehaviorsKernel->getContainer();
        }
>>>>>>> misc
=======
        $this->container = $doctrineBehaviorsKernel->getContainer();
>>>>>>> fixing blameable test

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

    /**
     * @return string[]
     */
    protected function provideCustomConfigs(): array
    {
        return [];
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
<<<<<<< HEAD
<<<<<<< HEAD
        return $this->container->get($type);
=======
        if (static::$container === null) {
            throw new ShouldNotHappenException();
        }

        return static::$container->get($type);
>>>>>>> misc
=======
        return $this->container->get($type);
>>>>>>> fixing blameable test
    }
}
