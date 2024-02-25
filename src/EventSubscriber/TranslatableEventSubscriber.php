<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\Persistence\ObjectManager;
use Knp\DoctrineBehaviors\Contract\Entity\TranslatableInterface;
use Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface;
use Knp\DoctrineBehaviors\Contract\Provider\LocaleProviderInterface;
use ReflectionClass;

#[AsDoctrineListener(event: Events::loadClassMetadata)]
#[AsDoctrineListener(event: Events::postLoad)]
#[AsDoctrineListener(event: Events::prePersist)]
final class TranslatableEventSubscriber
{
    /**
     * @var string
     */
    public const LOCALE = 'locale';

    private int $translatableFetchMode;

    private int $translationFetchMode;

    public function __construct(
        private LocaleProviderInterface $localeProvider,
        string $translatableFetchMode,
        string $translationFetchMode
    ) {
        $this->translatableFetchMode = $this->convertFetchString($translatableFetchMode);
        $this->translationFetchMode = $this->convertFetchString($translationFetchMode);
    }

    /**
     * Adds mapping to the translatable and translations.
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();
        if (! $classMetadata->reflClass instanceof ReflectionClass) {
            // Class has not yet been fully built, ignore this event
            return;
        }

        if ($classMetadata->isMappedSuperclass) {
            return;
        }

        if (is_a($classMetadata->reflClass->getName(), TranslatableInterface::class, true)) {
            $this->mapTranslatable($classMetadata);
        }

        if (is_a($classMetadata->reflClass->getName(), TranslationInterface::class, true)) {
            $this->mapTranslation($classMetadata, $loadClassMetadataEventArgs->getObjectManager());
        }
    }

    public function postLoad(PostLoadEventArgs $postLoadEventArgs): void
    {
        $this->setLocales($postLoadEventArgs);
    }

    public function prePersist(PrePersistEventArgs $prePersistEventArgs): void
    {
        $this->setLocales($prePersistEventArgs);
    }

    /**
     * Convert string FETCH mode to required string
     */
    private function convertFetchString(string|int $fetchMode): int
    {
        if (is_int($fetchMode)) {
            return $fetchMode;
        }

        if ($fetchMode === 'EAGER') {
            return ClassMetadataInfo::FETCH_EAGER;
        }

        if ($fetchMode === 'EXTRA_LAZY') {
            return ClassMetadataInfo::FETCH_EXTRA_LAZY;
        }

        return ClassMetadataInfo::FETCH_LAZY;
    }

    private function mapTranslatable(ClassMetadataInfo $classMetadataInfo): void
    {
        if ($classMetadataInfo->hasAssociation('translations')) {
            return;
        }

        $classMetadataInfo->mapOneToMany([
            'fieldName' => 'translations',
            'mappedBy' => 'translatable',
            'indexBy' => self::LOCALE,
            'cascade' => ['persist', 'merge', 'remove'],
            'fetch' => $this->translatableFetchMode,
            'targetEntity' => $classMetadataInfo->getReflectionClass()
                ->getMethod('getTranslationEntityClass')
                ->invoke(null),
            'orphanRemoval' => true,
        ]);
    }

    private function mapTranslation(ClassMetadataInfo $classMetadataInfo, ObjectManager $objectManager): void
    {
        if (! $classMetadataInfo->hasAssociation('translatable')) {
            $targetEntity = $classMetadataInfo->getReflectionClass()
                ->getMethod('getTranslatableEntityClass')
                ->invoke(null);

            /** @var ClassMetadataInfo $classMetadata */
            $classMetadata = $objectManager->getClassMetadata($targetEntity);

            $singleIdentifierFieldName = $classMetadata->getSingleIdentifierFieldName();

            $classMetadataInfo->mapManyToOne([
                'fieldName' => 'translatable',
                'inversedBy' => 'translations',
                'cascade' => ['persist', 'merge'],
                'fetch' => $this->translationFetchMode,
                'joinColumns' => [[
                    'name' => 'translatable_id',
                    'referencedColumnName' => $singleIdentifierFieldName,
                    'onDelete' => 'CASCADE',
                ]],
                'targetEntity' => $targetEntity,
            ]);
        }

        $name = $classMetadataInfo->getTableName() . '_unique_translation';
        if (! $this->hasUniqueTranslationConstraint($classMetadataInfo, $name) &&
            $classMetadataInfo->getName() === $classMetadataInfo->rootEntityName) {
            $classMetadataInfo->table['uniqueConstraints'][$name] = [
                'columns' => ['translatable_id', self::LOCALE],
            ];
        }

        if (! $classMetadataInfo->hasField(self::LOCALE) && ! $classMetadataInfo->hasAssociation(self::LOCALE)) {
            $classMetadataInfo->mapField([
                'fieldName' => self::LOCALE,
                'type' => 'string',
                'length' => 5,
            ]);
        }
    }

    private function setLocales(PostLoadEventArgs|PrePersistEventArgs $lifecycleEventArgs): void
    {
        $object = $lifecycleEventArgs->getObject();
        if (! $object instanceof TranslatableInterface) {
            return;
        }

        $currentLocale = $this->localeProvider->provideCurrentLocale();
        if ($currentLocale) {
            $object->setCurrentLocale($currentLocale);
        }

        $fallbackLocale = $this->localeProvider->provideFallbackLocale();
        if ($fallbackLocale) {
            $object->setDefaultLocale($fallbackLocale);
        }
    }

    private function hasUniqueTranslationConstraint(ClassMetadataInfo $classMetadataInfo, string $name): bool
    {
        return isset($classMetadataInfo->table['uniqueConstraints'][$name]);
    }
}
