<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\ORM\Translatable;

use Doctrine\DBAL\Platforms\PostgreSqlPlatform;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Id\AssignedGenerator;
use Doctrine\ORM\Id\BigIntegerIdentityGenerator;
use Doctrine\ORM\Id\IdentityGenerator;
use Doctrine\ORM\Id\SequenceGenerator;
use Doctrine\ORM\Id\UuidGenerator;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\ORMException;
use Knp\DoctrineBehaviors\ORM\AbstractSubscriber;
use Knp\DoctrineBehaviors\Reflection\ClassAnalyzer;

class TranslatableSubscriber extends AbstractSubscriber
{
    private $currentLocaleCallable;

    private $defaultLocaleCallable;

    private $translatableTrait;

    private $translationTrait;

    private $translatableFetchMode;

    private $translationFetchMode;

    public function __construct(
        ClassAnalyzer $classAnalyzer,
        ?callable $currentLocaleCallable = null,
        ?callable $defaultLocaleCallable = null,
        $translatableTrait,
        $translationTrait,
        $translatableFetchMode,
        $translationFetchMode
    ) {
        parent::__construct($classAnalyzer, false);

        $this->currentLocaleCallable = $currentLocaleCallable;
        $this->defaultLocaleCallable = $defaultLocaleCallable;
        $this->translatableTrait = $translatableTrait;
        $this->translationTrait = $translationTrait;
        $this->translatableFetchMode = $this->convertFetchString($translatableFetchMode);
        $this->translationFetchMode = $this->convertFetchString($translationFetchMode);
    }

    /**
     * Adds mapping to the translatable and translations.
     *
     * @param LoadClassMetadataEventArgs $loadClassMetadataEventArgs The event arguments
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();

        if ($classMetadata->reflClass === null) {
            return;
        }

        if ($this->isTranslatable($classMetadata)) {
            $this->mapTranslatable($classMetadata);
        }

        if ($this->isTranslation($classMetadata)) {
            $this->mapTranslation($classMetadata);
            $this->mapId($classMetadata, $loadClassMetadataEventArgs->getEntityManager());
        }
    }

    public function postLoad(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $this->setLocales($lifecycleEventArgs);
    }

    public function prePersist(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $this->setLocales($lifecycleEventArgs);
    }

    /**
     * Returns hash of events, that this subscriber is bound to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [Events::loadClassMetadata, Events::postLoad, Events::prePersist];
    }

    /**
     * Convert string FETCH mode to required string
     *
     * @param $fetchMode
     *
     * @return int
     */
    private function convertFetchString($fetchMode)
    {
        if (is_int($fetchMode)) {
            return $fetchMode;
        }

        switch ($fetchMode) {
            case 'LAZY':
                return ClassMetadataInfo::FETCH_LAZY;
            case 'EAGER':
                return ClassMetadataInfo::FETCH_EAGER;
            case 'EXTRA_LAZY':
                return ClassMetadataInfo::FETCH_EXTRA_LAZY;
            default:
                return ClassMetadataInfo::FETCH_LAZY;
        }
    }

    /**
     * Checks if entity is translatable
     *
     * @return boolean
     */
    private function isTranslatable(ClassMetadata $classMetadata)
    {
        return $this->getClassAnalyzer()->hasTrait($classMetadata->reflClass, $this->translatableTrait);
    }

    private function mapTranslatable(ClassMetadata $classMetadata): void
    {
        if (! $classMetadata->hasAssociation('translations')) {
            $classMetadata->mapOneToMany([
                'fieldName' => 'translations',
                'mappedBy' => 'translatable',
                'indexBy' => 'locale',
                'cascade' => ['persist', 'merge', 'remove'],
                'fetch' => $this->translatableFetchMode,
                'targetEntity' => $classMetadata->getReflectionClass()->getMethod('getTranslationEntityClass')->invoke(
                    null
                ),
                'orphanRemoval' => true,
            ]);
        }
    }

    /**
     * Checks if entity is a translation
     *
     * @return boolean
     */
    private function isTranslation(ClassMetadata $classMetadata)
    {
        return $this->getClassAnalyzer()->hasTrait($classMetadata->reflClass, $this->translationTrait);
    }

    private function mapTranslation(ClassMetadata $classMetadata): void
    {
        if (! $classMetadata->hasAssociation('translatable')) {
            $classMetadata->mapManyToOne([
                'fieldName' => 'translatable',
                'inversedBy' => 'translations',
                'cascade' => ['persist', 'merge'],
                'fetch' => $this->translationFetchMode,
                'joinColumns' => [[
                    'name' => 'translatable_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ]],
                'targetEntity' => $classMetadata->getReflectionClass()->getMethod('getTranslatableEntityClass')->invoke(
                    null
                ),
            ]);
        }

        $name = $classMetadata->getTableName() . '_unique_translation';
        if (! $this->hasUniqueTranslationConstraint($classMetadata, $name)) {
            $classMetadata->table['uniqueConstraints'][$name] = [
                'columns' => ['translatable_id', 'locale'],
            ];
        }

        if (! ($classMetadata->hasField('locale') || $classMetadata->hasAssociation('locale'))) {
            $classMetadata->mapField([
                'fieldName' => 'locale',
                'type' => 'string',
                'length' => 5,
            ]);
        }
    }

    /**
     * Kept for BC-compatibility purposes : people expect this lib to map ids for
     * translations.
     *
     * @deprecated It should be removed because it probably does not work with
     *             every doctrine version.
     *
     * @see https://github.com/doctrine/doctrine2/blob/0bff6aadbc9f3fd8167a320d9f4f6cf269382da0/lib/Doctrine/ORM/Mapping/ClassMetadataFactory.php#L508
     */
    private function mapId(ClassMetadata $classMetadata, EntityManager $entityManager): void
    {
        $platform = $entityManager->getConnection()->getDatabasePlatform();
        if (! $classMetadata->hasField('id')) {
            $builder = new ClassMetadataBuilder($classMetadata);
            $builder->createField('id', 'integer')->isPrimaryKey()->generatedValue()->build();
            /// START DOCTRINE CODE
            $idGenType = $classMetadata->generatorType;
            if ($idGenType === ClassMetadata::GENERATOR_TYPE_AUTO) {
                if ($platform->prefersSequences()) {
                    $classMetadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_SEQUENCE);
                } elseif ($platform->prefersIdentityColumns()) {
                    $classMetadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_IDENTITY);
                } else {
                    $classMetadata->setIdGeneratorType(ClassMetadata::GENERATOR_TYPE_TABLE);
                }
            }

            // Create & assign an appropriate ID generator instance
            switch ($classMetadata->generatorType) {
            case ClassMetadata::GENERATOR_TYPE_IDENTITY:
                // For PostgreSQL IDENTITY (SERIAL) we need a sequence name. It defaults to
                // <table>_<column>_seq in PostgreSQL for SERIAL columns.
                // Not pretty but necessary and the simplest solution that currently works.
                $sequenceName = null;
                $fieldName = $classMetadata->identifier ? $classMetadata->getSingleIdentifierFieldName() : null;

                if ($platform instanceof PostgreSqlPlatform) {
                    $columnName = $classMetadata->getSingleIdentifierColumnName();
                    $quoted = isset($classMetadata->fieldMappings[$fieldName]['quoted']) || isset($classMetadata->table['quoted']);
                    $sequenceName = $classMetadata->getTableName() . '_' . $columnName . '_seq';
                    $definition = [
                        'sequenceName' => $platform->fixSchemaElementName($sequenceName),
                    ];

                    if ($quoted) {
                        $definition['quoted'] = true;
                    }

                    $sequenceName = $entityManager->getConfiguration()->getQuoteStrategy()->getSequenceName(
                        $definition,
                        $classMetadata,
                        $platform
                    );
                }

                $generator = $fieldName && $classMetadata->fieldMappings[$fieldName]['type'] === 'bigint'
                    ? new BigIntegerIdentityGenerator($sequenceName)
                    : new IdentityGenerator($sequenceName);

                $classMetadata->setIdGenerator($generator);

                break;

            case ClassMetadata::GENERATOR_TYPE_SEQUENCE:
                // If there is no sequence definition yet, create a default definition
                $definition = $classMetadata->sequenceGeneratorDefinition;

                if (! $definition) {
                    $fieldName = $classMetadata->getSingleIdentifierFieldName();
                    $columnName = $classMetadata->getSingleIdentifierColumnName();
                    $quoted = isset($classMetadata->fieldMappings[$fieldName]['quoted']) || isset($classMetadata->table['quoted']);
                    $sequenceName = $classMetadata->getTableName() . '_' . $columnName . '_seq';
                    $definition = [
                        'sequenceName' => $platform->fixSchemaElementName($sequenceName),
                        'allocationSize' => 1,
                        'initialValue' => 1,
                    ];

                    if ($quoted) {
                        $definition['quoted'] = true;
                    }

                    $classMetadata->setSequenceGeneratorDefinition($definition);
                }

                $sequenceGenerator = new SequenceGenerator(
                    $entityManager->getConfiguration()->getQuoteStrategy()->getSequenceName(
                        $definition,
                        $classMetadata,
                        $platform
                    ),
                    $definition['allocationSize']
                );
                $classMetadata->setIdGenerator($sequenceGenerator);
                break;

            case ClassMetadata::GENERATOR_TYPE_NONE:
                $classMetadata->setIdGenerator(new AssignedGenerator());
                break;

            case ClassMetadata::GENERATOR_TYPE_UUID:
                $classMetadata->setIdGenerator(new UuidGenerator());
                break;

            case ClassMetadata::GENERATOR_TYPE_TABLE:
                throw new ORMException('TableGenerator not yet implemented.');
                break;

            case ClassMetadata::GENERATOR_TYPE_CUSTOM:
                $definition = $classMetadata->customGeneratorDefinition;
                if (! class_exists($definition['class'])) {
                    throw new ORMException("Can't instantiate custom generator : " . $definition['class']);
                }
                $classMetadata->setIdGenerator(new $definition['class']());
                break;

            default:
                throw new ORMException('Unknown generator type: ' . $classMetadata->generatorType);
            }
            /// END DOCTRINE COPY / PASTED code
        }
    }

    private function setLocales(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $entityManager = $lifecycleEventArgs->getEntityManager();
        $entity = $lifecycleEventArgs->getEntity();
        $classMetadata = $entityManager->getClassMetadata(get_class($entity));

        if (! $this->isTranslatable($classMetadata)) {
            return;
        }

        $currentLocale = $this->getCurrentLocale();
        if ($currentLocale) {
            $entity->setCurrentLocale($currentLocale);
        }

        $defaultLocale = $this->getDefaultLocale();
        if ($defaultLocale) {
            $entity->setDefaultLocale($defaultLocale);
        }
    }

    private function hasUniqueTranslationConstraint(ClassMetadata $classMetadata, $name)
    {
        if (! isset($classMetadata->table['uniqueConstraints'])) {
            return;
        }

        return isset($classMetadata->table['uniqueConstraints'][$name]);
    }

    private function getCurrentLocale()
    {
        $currentLocaleCallable = $this->currentLocaleCallable;

        if ($currentLocaleCallable) {
            return $currentLocaleCallable();
        }
    }

    private function getDefaultLocale()
    {
        $defaultLocaleCallable = $this->defaultLocaleCallable;

        if ($defaultLocaleCallable) {
            return $defaultLocaleCallable();
        }
    }
}
