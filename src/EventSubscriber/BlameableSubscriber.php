<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Doctrine\ORM\UnitOfWork;
use Knp\DoctrineBehaviors\Contract\Entity\BlameableInterface;
use Knp\DoctrineBehaviors\Contract\Provider\UserProviderInterface;

final class BlameableSubscriber implements EventSubscriber
{
    /**
     * @var string
     */
    private const DELETED_BY = 'deletedBy';

    /**
     * @var string
     */
    private const UPDATED_BY = 'updatedBy';

    /**
     * @var string
     */
    private const CREATED_BY = 'createdBy';

    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var UnitOfWork
     */
    private $unitOfWork;

    public function __construct(UserProviderInterface $userProvider, EntityManagerInterface $entityManager)
    {
        $this->userProvider = $userProvider;
        $this->unitOfWork = $entityManager->getUnitOfWork();
    }

    /**
     * Adds metadata about how to store user, either a string or an ManyToOne association on user entity
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();

        if (! is_a($classMetadata->reflClass->getName(), BlameableInterface::class, true)) {
            return;
        }

        $this->mapEntity($classMetadata);
    }

    /**
     * Stores the current user into createdBy and updatedBy properties
     */
    public function prePersist(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $entity = $lifecycleEventArgs->getEntity();
        if (! $entity instanceof BlameableInterface) {
            return;
        }

        $user = $this->userProvider->provideUser();
        // no user set → skip
        if ($user === null) {
            return;
        }

        if (! $entity->getCreatedBy()) {
            $entity->setCreatedBy($user);

            $this->unitOfWork->propertyChanged($entity, self::CREATED_BY, null, $user);
        }

        if (! $entity->getUpdatedBy()) {
            $entity->setUpdatedBy($user);

            $this->unitOfWork->propertyChanged($entity, self::UPDATED_BY, null, $user);
        }
    }

    /**
     * Stores the current user into updatedBy property
     */
    public function preUpdate(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $entity = $lifecycleEventArgs->getEntity();
        if (! $entity instanceof BlameableInterface) {
            return;
        }

        $user = $this->userProvider->provideUser();
        if ($user === null) {
            return;
        }

        $oldValue = $entity->getUpdatedBy();
        $entity->setUpdatedBy($user);

        $this->unitOfWork->propertyChanged($entity, self::UPDATED_BY, $oldValue, $user);
    }

    /**
     * Stores the current user into deletedBy property
     */
    public function preRemove(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $entity = $lifecycleEventArgs->getEntity();
        if (! $entity instanceof BlameableInterface) {
            return;
        }

        $user = $this->userProvider->provideUser();
        if ($user === null) {
            return;
        }

        $oldValue = $entity->getDeletedBy();
        $entity->setDeletedBy($user);

        $this->unitOfWork->propertyChanged($entity, self::DELETED_BY, $oldValue, $user);
    }

    public function getSubscribedEvents()
    {
        return [Events::prePersist, Events::preUpdate, Events::preRemove, Events::loadClassMetadata];
    }

    private function mapEntity(ClassMetadataInfo $classMetadataInfo): void
    {
        if ($this->userProvider->provideUserEntity()) {
            $this->mapManyToOneUser($classMetadataInfo);
        } else {
            $this->mapStringUser($classMetadataInfo);
        }
    }

    private function mapManyToOneUser(ClassMetadataInfo $classMetadataInfo): void
    {
        $userEntity = $this->userProvider->provideUserEntity();
        if ($userEntity === null) {
            return;
        }

        $this->mapManyToOneWithTargetEntity($classMetadataInfo, $userEntity, self::CREATED_BY);
        $this->mapManyToOneWithTargetEntity($classMetadataInfo, $userEntity, self::UPDATED_BY);
        $this->mapManyToOneWithTargetEntity($classMetadataInfo, $userEntity, self::DELETED_BY);
    }

    private function mapStringUser(ClassMetadataInfo $classMetadataInfo): void
    {
        $this->mapStringNullableField($classMetadataInfo, self::CREATED_BY);
        $this->mapStringNullableField($classMetadataInfo, self::UPDATED_BY);
        $this->mapStringNullableField($classMetadataInfo, self::DELETED_BY);
    }

    private function mapManyToOneWithTargetEntity(
        ClassMetadataInfo $classMetadataInfo,
        string $userEntity,
        string $fieldName
    ): void {
        if ($classMetadataInfo->hasAssociation($fieldName)) {
            return;
        }

        $classMetadataInfo->mapManyToOne([
            'fieldName' => $fieldName,
            'targetEntity' => $userEntity,
            'joinColumns' => [
                [
                    'onDelete' => 'SET NULL',
                ],
            ],
        ]);
    }

    private function mapStringNullableField(ClassMetadataInfo $classMetadataInfo, string $fieldName): void
    {
        if ($classMetadataInfo->hasField($fieldName)) {
            return;
        }

        $classMetadataInfo->mapField([
            'fieldName' => $fieldName,
            'type' => 'string',
            'nullable' => true,
        ]);
    }
}
