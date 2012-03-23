<?php

namespace Knp\DoctrineBehaviors\ORM\Blameable;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\SecurityContext;

use Doctrine\Common\EventSubscriber,
    Doctrine\ORM\Event\OnFlushEventArgs,
    Doctrine\ORM\Events;

class BlameableListener implements EventSubscriber
{
    private $securityContext;
    private $user;
    private $userEntity;

    public function __construct(SecurityContext $securityContext = null, $userEntity = null)
    {
        $this->securityContext = $securityContext;
        $this->userEntity = $userEntity;
    }

    public function loadClassMetadata(LoadClassMetadataEventArgs $eventArgs)
    {
        $classMetadata = $eventArgs->getClassMetadata();
        if ($this->isEntitySupported($classMetadata)) {
            if ($this->userEntity) {
                $classMetadata->mapManyToOne([
                    'fieldName'    => 'createdBy',
                    'targetEntity' => $this->userEntity,
                ]);
                $classMetadata->mapManyToOne([
                    'fieldName'    => 'updatedBy',
                    'targetEntity' => $this->userEntity,
                ]);
            }
            else {
                $classMetadata->mapField([
                    'fieldName'  => 'createdBy',
                    'type'       => 'string',
                    'nullable'   => true,
                ]);

                $classMetadata->mapField([
                    'fieldName'  => 'updatedBy',
                    'type'       => 'string',
                    'nullable'   => true,
                ]);
            }
        }
    }

    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        $classMetadata = $eventArgs->getEntityManager()->getClassMetadata(get_class($entity));
        if ($this->isEntitySupported($classMetadata)) {
            $entity->setCreatedBy($this->getUser());
            $entity->setUpdatedBy($this->getUser());
        }
    }

    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        $classMetadata = $eventArgs->getEntityManager()->getClassMetadata(get_class($entity));
        if ($this->isEntitySupported($classMetadata)) {
            $entity->setUpdatedBy($this->getUser());
        }
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        if (null !== $this->user) {
            return $this->user;
        }
        if (null === $this->securityContext) {
            return;
        }

        $token = $this->securityContext->getToken();

        if (null !== $token) {
            return $token->getUser();
        }
    }

    private function isEntitySupported(ClassMetadata $classMetadata)
    {
        return in_array('Knp\DoctrineBehaviors\ORM\Blameable\Blameable', $classMetadata->reflClass->getTraitNames());
    }

    public function getSubscribedEvents()
    {
        $events = [
            Events::prePersist,
            Events::preUpdate,
            Events::loadClassMetadata,
        ];

        return $events;
    }
}
