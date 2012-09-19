<?php

namespace Tests\Knp\DoctrineBehaviors\ORM;

use Doctrine\ORM\Mapping\Driver\AnnotationDriver;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\Common\EventManager;
use Doctrine\ORM\Mapping\DefaultQuoteStrategy;

trait EntityManagerProvider
{
    private $em;

    abstract protected function getUsedEntityFixtures();

    /**
     * EntityManager mock object together with
     * annotation mapping driver and pdo_sqlite
     * database in memory
     *
     * @param  EventManager  $evm
     * @return EntityManager
     */
    protected function getEntityManager(EventManager $evm = null, Configuration $config = null, array $conn = [])
    {
        if (null !== $this->em) {
            return $this->em;
        }

        $conn = array_merge(array(
            'driver' => 'pdo_sqlite',
            'memory' => true,
        ), $conn);

        $config = is_null($config) ? $this->getAnnotatedConfig() : $config;
        $em = EntityManager::create($conn, $config, $evm ?: $this->getEventManager());

        $schema = array_map(function($class) use ($em) {
            return $em->getClassMetadata($class);
        }, (array) $this->getUsedEntityFixtures());

        $schemaTool = new SchemaTool($em);
        $schemaTool->dropSchema($schema);
        $schemaTool->createSchema($schema);

        return $this->em = $em;
    }

    /**
     * Get annotation mapping configuration
     *
     * @return Doctrine\ORM\Configuration
     */
    protected function getAnnotatedConfig()
    {
        // We need to mock every method except the ones which
        // handle the filters
        $configurationClass = 'Doctrine\ORM\Configuration';
        $refl = new \ReflectionClass($configurationClass);
        $methods = $refl->getMethods();

        $mockMethods = array();

        foreach ($methods as $method) {
            if (!in_array($method->name, ['addFilter', 'getFilterClassName', 'addCustomNumericFunction', 'getCustomNumericFunction'])) {
                $mockMethods[] = $method->name;
            }
        }

        $config = $this->getMock($configurationClass, $mockMethods);

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
            ->will($this->returnValue('Doctrine\\ORM\\Mapping\\ClassMetadataFactory'))
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
            ->will($this->returnValue('Doctrine\\ORM\\EntityRepository'))
        ;

        $config
            ->expects($this->any())
            ->method('getQuoteStrategy')
            ->will($this->returnValue(new DefaultQuoteStrategy))
        ;

        return $config;
    }

    /**
     * Creates default mapping driver
     *
     * @return \Doctrine\ORM\Mapping\Driver\Driver
     */
    protected function getMetadataDriverImplementation()
    {
        return new AnnotationDriver($_ENV['annotation_reader']);
    }

    /**
     * Build event manager
     *
     * @return EventManager
     */
    protected function getEventManager()
    {
        return new EventManager;
    }
}
