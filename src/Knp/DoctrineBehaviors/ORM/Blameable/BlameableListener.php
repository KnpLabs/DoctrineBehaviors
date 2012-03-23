<?php

/**
 * This file is part of the KnpDoctrineBehaviors package.
 *
 * (c) KnpLabs <http://knplabs.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knp\DoctrineBehaviors\ORM\Blameable;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\ORM\Event\LoadClassMetadataEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\Security\Core\SecurityContext;

use Doctrine\Common\EventSubscriber,
    Doctrine\ORM\Event\OnFlushEventArgs,
    Doctrine\ORM\Events;

/**
 * BlameableListener handle Blameable entites
 * Adds class metadata depending of user type (entity or string)
 * Listens to prePersist and PreUpdate lifecycle events
 */
class BlameableListener implements EventSubscriber
{
    /**
     * @var SecurityContextInterface|null
     */
    private $securityContext;

    /**
     * @var UserInterface|string
     */
    private $user;

    /**
     * userEntity name
     */
    private $userEntity;

    /**
     * @constructor
     *
     * @param SecurityContextInterface $securityContext
     * @param string $userEntity
     */
    public function __construct(SecurityContextInterface $securityContext = null, $userEntity = null)
    {
        $this->securityContext = $securityContext;
        $this->userEntity = $userEntity;
    }

    /**
     * Adds metadata about how to store user, either a string or an ManyToOne association on UserInterface entity
     *
     * @param LoadClassMetadataEventArgs $eventArgs
     */
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

    /**
     * Stores the current user into createdBy and updatedBy properties
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        $classMetadata = $eventArgs->getEntityManager()->getClassMetadata(get_class($entity));
        if ($this->isEntitySupported($classMetadata)) {
            $entity->setCreatedBy($this->getUser());
            $entity->setUpdatedBy($this->getUser());
        }
    }

    /**
     * Stores the current user into updatedBy property
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function preUpdate(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getEntity();
        $classMetadata = $eventArgs->getEntityManager()->getClassMetadata(get_class($entity));
        if ($this->isEntitySupported($classMetadata)) {
            $entity->setUpdatedBy($this->getUser());
        }
    }

    /**
     * set a custome representation of current user
     *
     * @param mixed $user
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * get current user, either if $this->user is present or from SecurityContext
     *
     * @return mixed The user reprensentation
     */
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

    /**
     * Checks if entity supports Blameable
     *
     * @param ClassMetadata $classMetadata
     * @return boolean
     */
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
