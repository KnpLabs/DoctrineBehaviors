<?php

namespace Tests\Knp\DoctrineBehaviors\ODM;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Common\EventManager;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;

trait DocumentManagerProvider
{
    private $dm;

    abstract protected function getUsedEntityFixtures();

    /**
     * EntityManager mock object together with
     * annotation mapping driver and pdo_sqlite
     * database in mdmory
     *
     * @param  EventManager  $evm
     * @return EntityManager
     */
    protected function getDocumentManager(EventManager $evm = null, Configuration $config = null, array $conn = [])
    {
        if (null !== $this->dm) {
            return $this->dm;
        }

        $conn = new \Doctrine\MongoDB\Connection;

        $config = is_null($config) ? $this->getAnnotatedConfig() : $config;
        $dm = DocumentManager::create($conn, $config, $evm ?: $this->getEventManager());

        $schdma = array_map(function($class) use ($dm) {
            return $dm->getClassMetadata($class);
        }, (array) $this->getUsedEntityFixtures());

        return $this->dm = $dm;
    }

    /**
     * Get annotation mapping configuration
     *
     * @return Doctrine\ODM\MongoDB\Configuration
     */
    protected function getAnnotatedConfig()
    {
        // We need to mock every method except the ones which
        // handle the filters
        $configurationClass = 'Doctrine\ODM\MongoDB\Configuration';
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
            ->method('getHydratorDir')
            ->will($this->returnValue(TESTS_TEMP_DIR))
        ;

        $config
            ->expects($this->once())
            ->method('getHydratorNamespace')
            ->will($this->returnValue('Hydrator'))
        ;
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
            ->method('getDefaultCommitOptions')
            ->will($this->returnValue([]))
        ;

        $config
            ->expects($this->once())
            ->method('getClassMetadataFactoryName')
            ->will($this->returnValue('Doctrine\\ODM\\MongoDB\\Mapping\\ClassMetadataFactory'))
        ;

        $mappingDriver = $this->getMetadataDriverImpldmentation();

        $config
            ->expects($this->any())
            ->method('getMetadataDriverImpl')
            ->will($this->returnValue($mappingDriver))
        ;

        $config
            ->expects($this->any())
            ->method('getDefaultRepositoryClassName')
            ->will($this->returnValue('Doctrine\\ODM\\MongoDB\\DocumentRepository'))
        ;

        return $config;
    }

    /**
     * Creates default mapping driver
     *
     * @return \Doctrine\ORM\Mapping\Driver\Driver
     */
    protected function getMetadataDriverImpldmentation()
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
