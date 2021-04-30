<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Versionable\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\Version;
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

    public function onFlush(OnFlushEventArgs $args): void
    {
        $unitOfWork = $this->entityManager->getUnitOfWork();

        $resourceClass = $this->entityManager->getClassMetadata(ResourceVersion::class);

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            if (! $entity instanceof VersionableInterface) {
                continue;
            }

            $entityClass = $this->entityManager->getClassMetadata(get_class($entity));
            $this->ensureEntityHasVersionProperty($entityClass);

            $entityId = $this->resolveEntityId($entityClass, $entity);

            $oldValues = array_map(function (array $changeSetField) {
                return $changeSetField[0];
            }, $unitOfWork->getEntityChangeSet($entity));

            if (! isset($entityClass->reflFields[$entityClass->versionField])) {
                continue;
            }

            $entityVersion = $entityClass->reflFields[$entityClass->versionField]->getValue($entity);

            unset($oldValues[$entityClass->versionField]);
            unset($oldValues[$entityClass->getSingleIdentifierFieldName()]);

            $resourceVersion = new ResourceVersion($entityClass->name, $entityId, $oldValues, $entityVersion);

            $this->entityManager->persist($resourceVersion);
            $unitOfWork->computeChangeSet($resourceClass, $resourceVersion);
        }
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
