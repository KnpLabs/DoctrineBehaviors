<?php

namespace Tests\Knp\DoctrineBehaviors\ODM;

use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\Common\EventManager;
use Doctrine\ODM\MongoDB\Configuration;
use Doctrine\ODM\MongoDB\Mapping\Driver\AnnotationDriver;
use Doctrine\Common\Annotations\AnnotationReader;

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
        $config = new Configuration;

        $config->setProxyDir(TESTS_TEMP_DIR);
        $config->setProxyNamespace('Proxy');

        $config->setHydratorDir(TESTS_TEMP_DIR);
        $config->setHydratorNamespace('Hydrator');

        $config->setDefaultDB('doctrine_behavior_test');

        $reader = new AnnotationReader();
        $config->setMetadataDriverImpl(new AnnotationDriver($reader, __DIR__ . '/Documents'));

        return $config;
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
