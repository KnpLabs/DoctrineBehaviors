<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Versionable;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query;
use Knp\DoctrineBehaviors\Versionable\Contract\VersionableInterface;
use Knp\DoctrineBehaviors\Versionable\Entity\ResourceVersion;
use Knp\DoctrineBehaviors\Versionable\Exception\VersionableException;

final class VersionManager
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
     * Return all versions of an versionable entity
     *
     * @return array<int, ResourceVersion>
     */
    public function getVersions(VersionableInterface $versionable): array
    {
        $classMetadataFactory = $this->entityManager->getMetadataFactory();
        $versionableClassName = get_class($versionable);
        $versionableClassMetadata = $classMetadataFactory->getMetadataFor($versionableClassName);

        $identifierValues = $versionableClassMetadata->getIdentifierValues($versionable);

        $resourceId = current($identifierValues);

        $query = $this->createQuery($versionable, $resourceId);

        $resourceVersions = [];

        /** @var ResourceVersion $resourceVersion */
        foreach ($query->getResult() as $resourceVersion) {
            $resourceVersions[$resourceVersion->getVersion()] = $resourceVersion;
        }

        return $resourceVersions;
    }

    public function revert(VersionableInterface $versionable, int $targetVersionNumber): void
    {
        $resourceVersions = $this->getVersions($versionable);

        if (! isset($resourceVersions[$targetVersionNumber])) {
            $errorMessage = sprintf('Version "%s" was not found', $targetVersionNumber);
            throw new VersionableException($errorMessage);
        }

        $resourceVersion = $resourceVersions[$targetVersionNumber];

        $versionableClassMetadata = $this->entityManager->getClassMetadata(get_class($versionable));

        foreach ($resourceVersion->getVersionedData() as $key => $value) {
            if (! isset($versionableClassMetadata->reflFields[$key])) {
                continue;
            }

            $versionableClassMetadata->reflFields[$key]->setValue($versionable, $value);
        }

        if ($versionableClassMetadata->changeTrackingPolicy === ClassMetadata::CHANGETRACKING_DEFERRED_EXPLICIT) {
            $this->entityManager->persist($versionable);
        }
    }

    private function createQuery(VersionableInterface $versionable, $resourceId): Query
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        $queryBuilder->select('v')
            ->from(ResourceVersion::class, 'v', 'v.version')
            ->andWhere('v.resourceName = :resourceName')
            ->setParameter('resourceName', get_class($versionable))
            ->andWhere('v.resourceId = :resourceId')
            ->setParameter('resourceId', $resourceId)
            ->orderBy('v.version', 'DESC');

        return $queryBuilder->getQuery();
    }
}
