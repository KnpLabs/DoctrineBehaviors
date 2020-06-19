<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Hashids\HashidsInterface;
use Knp\DoctrineBehaviors\Contract\Entity\HashidableInterface;

final class HashidableEventSubscriber implements EventSubscriber
{
    /**
     * @var string
     */
    private const HASH_ID_FIELD_NAME = 'hashId';

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var HashidsInterface
     */
    private $hashids;

    public function __construct(EntityManagerInterface $entityManager, HashidsInterface $hashids)
    {
        $this->entityManager = $entityManager;
        $this->hashids = $hashids;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $loadClassMetadataEventArgs): void
    {
        $classMetadata = $loadClassMetadataEventArgs->getClassMetadata();
        if ($classMetadata->reflClass === null) {
            // Class has not yet been fully built, ignore this event
            return;
        }

        if (! is_a($classMetadata->reflClass->getName(), HashidableInterface::class, true)) {
            return;
        }

        $classMetadata->mapField([
            'fieldName' => self::HASH_ID_FIELD_NAME,
            'type' => 'string',
            'nullable' => true,
            'unique' => true,
        ]);
    }

    /**
     * @return string[]
     */
    public function getSubscribedEvents(): array
    {
        return [Events::loadClassMetadata, Events::postPersist];
    }

    public function postPersist(LifecycleEventArgs $lifecycleEventArgs): void
    {
        $entity = $lifecycleEventArgs->getObject();
        if (! $entity instanceof HashidableInterface) {
            return;
        }

        $hashId = $this->hashids->encode($this->resolveFieldValue($entity->getHashidableField(), $entity));
        $entity->setHashId($hashId);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    private function resolveFieldValue(string $field, $entity)
    {
        $methodName = 'get' . ucfirst($field);
        if (method_exists($entity, $methodName)) {
            return $entity->{$methodName}();
        }

        return null;
    }
}
