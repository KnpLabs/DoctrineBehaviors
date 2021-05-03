<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Versionable\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Version;
use Doctrine\ORM\UnitOfWork;
use Knp\DoctrineBehaviors\Versionable\Contract\VersionableInterface;
use Knp\DoctrineBehaviors\Versionable\Entity\ResourceVersion;
use Knp\DoctrineBehaviors\Versionable\Exception\VersionableException;

final class VersionableEventSubscriber implements EventSubscriber
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [Events::onFlush];
    }

    public function onFlush(OnFlushEventArgs $onFlushEventArgs): void
    {
        $entityManager = $onFlushEventArgs->getEntityManager();
        $unitOfWork = $entityManager->getUnitOfWork();

        $versionableEntities = $this->resolveUpdatedVersionableEntities($unitOfWork);
        if ($versionableEntities === []) {
            return;
        }

        $resourceVersionClassMetadata = $entityManager->getClassMetadata(ResourceVersion::class);

        foreach ($versionableEntities as $versionableEntity) {
            $entityClassMetadata = $entityManager->getClassMetadata(get_class($versionableEntity));
            $this->ensureEntityHasVersionProperty($entityClassMetadata);

            $entityId = $this->resolveEntityId($entityClassMetadata, $versionableEntity);

            $oldValues = array_map(function (array $changeSetField) {
                return $changeSetField[0];
            }, $unitOfWork->getEntityChangeSet($versionableEntity));

            if (! isset($entityClassMetadata->reflFields[$entityClassMetadata->versionField])) {
                continue;
            }

            $entityVersion = $entityClassMetadata->reflFields[$entityClassMetadata->versionField]->getValue(
                $versionableEntity
            );

            unset($oldValues[$entityClassMetadata->versionField]);
            unset($oldValues[$entityClassMetadata->getSingleIdentifierFieldName()]);

            $resourceVersion = new ResourceVersion($entityClassMetadata->name, $entityId, $oldValues, $entityVersion);
            $this->entityManager->persist($resourceVersion);

            $unitOfWork->computeChangeSet($resourceVersionClassMetadata, $resourceVersion);
        }
    }

    /**
     * @return VersionableInterface[]
     */
    private function resolveUpdatedVersionableEntities(UnitOfWork $unitOfWork): array
    {
        return array_filter($unitOfWork->getScheduledEntityUpdates(), function (object $entity) {
            return $entity instanceof VersionableInterface;
        });
    }

    private function ensureEntityHasVersionProperty(ClassMetadata $classMetadata): void
    {
        if ($classMetadata->isVersioned) {
            return;
        }

        throw new VersionableException(sprintf(
            'Property "$version" with "%s" annotation is missing in "%s" entity',
            Version::class,
            $classMetadata->getName()
        ));
    }

    private function resolveEntityId(ClassMetadata $classMetadata, VersionableInterface $versionable)
    {
        $entityId = $classMetadata->getIdentifierValues($versionable);
        if (count($entityId) === 1 && current($entityId)) {
            return current($entityId);
        }

        throw new VersionableException('A single identifier column is required for the Versionable extension.');
    }
}
