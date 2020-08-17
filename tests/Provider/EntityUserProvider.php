<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Knp\DoctrineBehaviors\Contract\Provider\UserProviderInterface;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\UserEntity;

final class EntityUserProvider implements UserProviderInterface
{
    /**
     * @var bool
     */
    private $isUserEntityPrepared = false;

    /**
     * @var UserEntity[]
     */
    private $userEntities = [];

    /**
     * @var UserEntity
     */
    private $userEntity;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function changeUser($userEntity): void
    {
        if ($this->userEntities !== [] && array_key_exists($userEntity, $this->userEntities)) {
            $this->userEntity = $this->userEntities[$userEntity];
        }
    }

    public function provideUser()
    {
        $this->prepareUserEntities();

        return $this->userEntity;
    }

    public function provideUserEntity(): ?string
    {
        return UserEntity::class;
    }

    private function prepareUserEntities(): void
    {
        if ($this->isUserEntityPrepared) {
            return;
        }

        $userEntity = new UserEntity();
        $userEntity->setUsername('user');

        $user2Entity = new UserEntity();
        $user2Entity->setUsername('user2');

        // persist user
        $this->entityManager->persist($userEntity);
        $this->entityManager->persist($user2Entity);
        $this->entityManager->flush();

        $this->userEntities['user'] = $userEntity;
        $this->userEntities['user2'] = $user2Entity;

        $this->userEntity = $userEntity;

        $this->isUserEntityPrepared = true;
    }
}
