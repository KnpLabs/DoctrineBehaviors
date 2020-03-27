<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\EventSubscriber;

use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Events;
use Hashids\HashidsInterface;
use Knp\DoctrineBehaviors\Contract\Entity\HashidableInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class HashidableSubscriber implements EventSubscriber
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
     * @var ContainerInterface
     */
    private $container;

    public function __construct(EntityManagerInterface $entityManager, ContainerInterface $container)
    {
        $this->entityManager = $entityManager;
        $this->container = $container;
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
            'type' => Types::STRING,
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
        $entity = $lifecycleEventArgs->getEntity();
        if (! $entity instanceof HashidableInterface) {
            return;
        }

        /** @var HashidsInterface $hashIds */
        $hashids = $this->container->get('hashids');
        $hashId = $hashids->encode($this->resolveFieldValue($entity->getHashidableField()));

        $entity->setHashId($hashId);

        $this->entityManager->persist($entity);
        $this->entityManager->flush();
    }

    private function resolveFieldValue(string $field)
    {
        if (property_exists($this, $field)) {
            return $this->{$field};
        }

        $methodName = 'get' . ucfirst($field);
        if (method_exists($this, $methodName)) {
            return $this->{$methodName}();
        }

        return null;
    }
}
