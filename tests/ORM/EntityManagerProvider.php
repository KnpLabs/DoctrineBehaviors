<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\ORM;

use Doctrine\Common\EventManager;
use Doctrine\ORM\Configuration;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Mapping\DefaultQuoteStrategy;
use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\Mapping\Driver\Driver;
use Doctrine\ORM\Repository\DefaultRepositoryFactory;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\MockObject\MockBuilder;
use ReflectionClass;

/**
 * @property-read $this \PHPUnit\Framework\TestCase
 */
trait EntityManagerProvider
{
    private $em;

    abstract protected function getUsedEntityFixtures();

    /**
     * EntityManager mock object together with
     * annotation mapping driver and pdo_sqlite
     * database in memory
     *
     * @param  EventManager  $eventManager
     * @return EntityManager
     */
    protected function getEntityManager(
        ?EventManager $eventManager = null,
        ?Configuration $configuration = null,
        array $conn = []
    ) {
        if ($this->em !== null) {
            return $this->em;
        }

        $conn = array_merge([
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ], $conn);

        $configuration = $configuration === null ? $this->getAnnotatedConfig() : $configuration;
        $entityManager = EntityManager::create($conn, $configuration, $eventManager ?: $this->getEventManager());

        $schema = array_map(function ($class) use ($entityManager) {
            return $entityManager->getClassMetadata($class);
        }, (array) $this->getUsedEntityFixtures());

        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema($schema);
        $schemaTool->createSchema($schema);

        return $this->em = $entityManager;
    }

    /**
     * EntityManager mock object together with
     * annotation mapping driver and engine given
     * by DB_ENGINE (pdo_mysql or pdo_pgsql)
     * database in memory
     */
    protected function getDBEngineEntityManager(): EntityManager
    {
        if (DB_ENGINE === 'pgsql') {
            return $this->getEntityManager(
                null,
                null,
                [
                    'driver' => 'pdo_pgsql',
                    'host' => DB_HOST,
                    'dbname' => DB_NAME,
                    'user' => DB_USER,
                    'password' => DB_PASSWD,
                ]
            );
        }
        return $this->getEntityManager(
                null,
                null,
                [
                    'driver' => 'pdo_mysql',
                    'host' => DB_HOST,
                    'dbname' => DB_NAME,
                    'user' => DB_USER,
                    'password' => DB_PASSWD,
                ]
            );
    }

    protected function getAnnotatedConfig(): Configuration
    {
        // We need to mock every method except the ones which
        // handle the filters
        $configurationClass = Configuration::class;
        $refl = new ReflectionClass($configurationClass);
        $methods = $refl->getMethods();

        $mockMethods = [];

        foreach ($methods as $method) {
            if (! in_array(
                $method->name,
                ['addFilter', 'getFilterClassName', 'addCustomNumericFunction', 'getCustomNumericFunction'],
                true
            )) {
                $mockMethods[] = $method->name;
            }
        }

        /** @var MockBuilder $mockBuilder */
        $mockBuilder = $this->getMockBuilder($configurationClass);
        $mockBuilder->addMethods($mockMethods);

        $config = $mockBuilder->getMock();

        $config
            ->expects($this->once())
            ->method('getProxyDir')
            ->will($this->returnValue(TESTS_TEMP_DIR))
        ;

        $config
            ->expects($this->once())
            ->method('getProxyNamespace')
            ->will($this->returnValue('Proxy'))
        ;

        $config
            ->expects($this->once())
            ->method('getAutoGenerateProxyClasses')
            ->will($this->returnValue(true))
        ;

        $config
            ->expects($this->once())
            ->method('getClassMetadataFactoryName')
            ->will($this->returnValue(ClassMetadataFactory::class))
        ;

        $mappingDriver = $this->getMetadataDriverImplementation();

        $config
            ->expects($this->any())
            ->method('getMetadataDriverImpl')
            ->will($this->returnValue($mappingDriver))
        ;

        $config
            ->expects($this->any())
            ->method('getDefaultRepositoryClassName')
            ->will($this->returnValue(EntityRepository::class))
        ;

        if (class_exists(DefaultQuoteStrategy::class)) {
            $config
                ->expects($this->any())
                ->method('getQuoteStrategy')
                ->will($this->returnValue(new DefaultQuoteStrategy()))
            ;
        }

        if (class_exists(DefaultRepositoryFactory::class)) {
            $config
                ->expects($this->any())
                ->method('getRepositoryFactory')
                ->will($this->returnValue(new DefaultRepositoryFactory()))
            ;
        }

        $config
            ->expects($this->any())
            ->method('getDefaultQueryHints')
            ->will($this->returnValue([]))
        ;

        return $config;
    }

    protected function getMetadataDriverImplementation(): AnnotationDriver
    {
        return new AnnotationDriver($_ENV['annotation_reader']);
    }

    protected function getEventManager(): EventManager
    {
        return new EventManager();
    }
}
