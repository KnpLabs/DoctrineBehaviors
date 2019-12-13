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

    public function provideUser()
    {
        $this->prepareUserEntity();

        return $this->userEntity;
    }

    public function provideUserEntity(): ?string
    {
        return UserEntity::class;
    }

    private function prepareUserEntity(): void
    {
        if ($this->isUserEntityPrepared) {
            return;
        }

        $userEntity = new UserEntity();
        $userEntity->setUsername('some_user_name');

        // persist user
        $this->entityManager->persist($userEntity);
        $this->entityManager->flush();

        $this->userEntity = $userEntity;

        $this->isUserEntityPrepared = true;
    }
}
