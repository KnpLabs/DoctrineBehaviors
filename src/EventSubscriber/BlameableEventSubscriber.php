<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Knp\DoctrineBehaviors\Contract\Entity\BlameableInterface;
use Knp\DoctrineBehaviors\Contract\Provider\UserProviderInterface;

#[AsDoctrineListener(event: Events::loadClassMetadata)]
#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
#[AsDoctrineListener(event: Events::preRemove)]
final class BlameableEventSubscriber
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

    public function __construct(
        private UserProviderInterface $userProvider,
        private EntityManagerInterface $entityManager,
        private ?string $blameableUserEntity = null
    ) {
    }

    /**
     * Adds metadata about how to store user, either a string or an ManyToOne association on user entity
     */
    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();
        if ($classMetadata->reflClass === null) {
            // Class has not yet been fully built, ignore this event
            return;
        }

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
        $entity = $lifecycleEventArgs->getObject();
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

            $this->getUnitOfWork()
                ->propertyChanged($entity, self::CREATED_BY, null, $user);
        }

        if (! $entity->getUpdatedBy()) {
            $entity->setUpdatedBy($user);

            $this->getUnitOfWork()
                ->propertyChanged($entity, self::UPDATED_BY, null, $user);
        }
    }

    /**
     * Stores the current user into updatedBy property
     */
    public function preUpdate(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $entity = $lifecycleEventArgs->getObject();
        if (! $entity instanceof BlameableInterface) {
            return;
        }

        $user = $this->userProvider->provideUser();
        if ($user === null) {
            return;
        }

        $oldValue = $entity->getUpdatedBy();
        $entity->setUpdatedBy($user);

        $this->getUnitOfWork()
            ->propertyChanged($entity, self::UPDATED_BY, $oldValue, $user);
    }

    /**
     * Stores the current user into deletedBy property
     */
    public function preRemove(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $entity = $lifecycleEventArgs->getObject();
        if (! $entity instanceof BlameableInterface) {
            return;
        }

        $user = $this->userProvider->provideUser();
        if ($user === null) {
            return;
        }

        $oldDeletedBy = $entity->getDeletedBy();
        $entity->setDeletedBy($user);

        $this->getUnitOfWork()
            ->propertyChanged($entity, self::DELETED_BY, $oldDeletedBy, $user);
    }

    private function mapEntity(ClassMetadata $classMetadata): void
    {
        if ($this->blameableUserEntity !== null && class_exists($this->blameableUserEntity)) {
            $this->mapManyToOneUser($classMetadata);
        } else {
            $this->mapStringUser($classMetadata);
        }
    }

    private function getUnitOfWork(): UnitOfWork
    {
        return $this->entityManager->getUnitOfWork();
    }

    private function mapManyToOneUser(ClassMetadata $classMetadata): void
    {
        $this->mapManyToOneWithTargetEntity($classMetadata, self::CREATED_BY);
        $this->mapManyToOneWithTargetEntity($classMetadata, self::UPDATED_BY);
        $this->mapManyToOneWithTargetEntity($classMetadata, self::DELETED_BY);
    }

    private function mapStringUser(ClassMetadata $classMetadata): void
    {
        $this->mapStringNullableField($classMetadata, self::CREATED_BY);
        $this->mapStringNullableField($classMetadata, self::UPDATED_BY);
        $this->mapStringNullableField($classMetadata, self::DELETED_BY);
    }

    private function mapManyToOneWithTargetEntity(ClassMetadata $classMetadata, string $fieldName): void
    {
        if ($classMetadata->hasAssociation($fieldName)) {
            return;
        }

        $classMetadata->mapManyToOne([
            'fieldName' => $fieldName,
            'targetEntity' => $this->blameableUserEntity,
            'joinColumns' => [
                [
                    'onDelete' => 'SET NULL',
                ],
            ],
        ]);
    }

    private function mapStringNullableField(ClassMetadata $classMetadata, string $fieldName): void
    {
        if ($classMetadata->hasField($fieldName)) {
            return;
        }

        $classMetadata->mapField([
            'fieldName' => $fieldName,
            'type' => 'string',
            'nullable' => true,
        ]);
    }
}
