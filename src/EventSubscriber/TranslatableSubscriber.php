<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\Builder\ClassMetadataBuilder;
use Doctrine\ORM\Mapping\ClassMetadataFactory;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Contract\Provider\LocaleProviderInterface;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;

final class TranslatableSubscriber implements EventSubscriber
{
    /**
     * @var int
     */
    private $translatableFetchMode;

    /**
     * @var int
     */
    private $translationFetchMode;

    /**
     * @var LocaleProviderInterface
     */
    private $localeProvider;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        EntityManagerInterface $entityManager,
        LocaleProviderInterface $localeProvider,
        string $translatableFetchMode,
        string $translationFetchMode
    ) {
        $this->entityManager = $entityManager;
        $this->localeProvider = $localeProvider;
        $this->translatableFetchMode = $this->convertFetchString($translatableFetchMode);
        $this->translationFetchMode = $this->convertFetchString($translationFetchMode);
    }

    /**
     * Adds mapping to the translatable and translations.
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();

        if ($classMetadata->isMappedSuperclass) {
            return;
        }

        if ($this->isTranslatable($classMetadata)) {
            $this->mapTranslatable($classMetadata);
        }

        if ($this->isTranslation($classMetadata)) {
            $this->mapTranslation($classMetadata);
            $this->mapId($classMetadata);
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
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [Events::loadClassMetadata, Events::postLoad, Events::prePersist];
    }

    /**
     * Convert string FETCH mode to required string
     */
    private function convertFetchString($fetchMode): int
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

    private function isTranslatable(ClassMetadataInfo $classMetadataInfo): bool
    {
        return is_a($classMetadataInfo->reflClass->getName(), TranslatableInterface::class, true);
    }

    private function mapTranslatable(ClassMetadataInfo $classMetadataInfo): void
    {
        if ($classMetadataInfo->hasAssociation('translations')) {
            return;
        }

        $classMetadataInfo->mapOneToMany([
            'fieldName' => 'translations',
            'mappedBy' => 'translatable',
            'indexBy' => 'locale',
            'cascade' => ['persist', 'merge', 'remove'],
            'fetch' => $this->translatableFetchMode,
            'targetEntity' => $classMetadataInfo->getReflectionClass()->getMethod('getTranslationEntityClass')->invoke(
                null
            ),
            'orphanRemoval' => true,
        ]);
    }

    private function isTranslation(ClassMetadataInfo $classMetadataInfo): bool
    {
        return is_a($classMetadataInfo->reflClass->getName(), TranslationInterface::class, true);
    }

    private function mapTranslation(ClassMetadataInfo $classMetadataInfo): void
    {
        if (! $classMetadataInfo->hasAssociation('translatable')) {
            $classMetadataInfo->mapManyToOne([
                'fieldName' => 'translatable',
                'inversedBy' => 'translations',
                'cascade' => ['persist', 'merge'],
                'fetch' => $this->translationFetchMode,
                'joinColumns' => [[
                    'name' => 'translatable_id',
                    'referencedColumnName' => 'id',
                    'onDelete' => 'CASCADE',
                ]],
                'targetEntity' => $classMetadataInfo->getReflectionClass()->getMethod(
                    'getTranslatableEntityClass'
                )->invoke(null),
            ]);
        }

        $name = $classMetadataInfo->getTableName() . '_unique_translation';
        if (! $this->hasUniqueTranslationConstraint($classMetadataInfo, $name)) {
            $classMetadataInfo->table['uniqueConstraints'][$name] = [
                'columns' => ['translatable_id', 'locale'],
            ];
        }

        if (! $classMetadataInfo->hasField('locale') && ! $classMetadataInfo->hasAssociation('locale')) {
            $classMetadataInfo->mapField([
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
     * @see https://github.com/doctrine/doctrine2/blob/0bff6aadbc9f3fd8167a320d9f4f6cf269382da0/lib/Doctrine/ORM/Mapping/ClassMetadataFactory.php#L508
     */
    private function mapId(ClassMetadataInfo $classMetadataInfo): void
    {
        // skip if already has id property
        if ($classMetadataInfo->hasField('id')) {
            return;
        }

        $builder = new ClassMetadataBuilder($classMetadataInfo);
        $builder->createField('id', 'integer')
            ->makePrimaryKey()
            ->generatedValue()
            ->build();

        $metadataFactory = $this->entityManager->getMetadataFactory();
        $privatesCaller = new PrivatesCaller();
        $privatesCaller->callPrivateMethod($metadataFactory, 'completeIdGeneratorMapping', $classMetadataInfo);
    }

    private function setLocales(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $entity = $lifecycleEventArgs->getEntity();
        if (! $entity instanceof TranslatableInterface) {
            return;
        }

        $currentLocale = $this->localeProvider->provideCurrentLocale();
        if ($currentLocale) {
            $entity->setCurrentLocale($currentLocale);
        }

        $fallbackLocale = $this->localeProvider->provideFallbackLocale();
        if ($fallbackLocale) {
            $entity->setDefaultLocale($fallbackLocale);
        }
    }

    private function hasUniqueTranslationConstraint(ClassMetadataInfo $classMetadataInfo, string $name): bool
    {
        if (! isset($classMetadataInfo->table['uniqueConstraints'])) {
            return false;
        }

        return isset($classMetadataInfo->table['uniqueConstraints'][$name]);
    }
}
