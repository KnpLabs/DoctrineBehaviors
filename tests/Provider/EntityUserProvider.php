<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Provider;

use Doctrine\ORM\EntityManagerInterface;
use Knp\DoctrineBehaviors\Contract\Provider\UserProviderInterface;
use Knp\DoctrineBehaviors\Exception\ShouldNotHappenException;
use Knp\DoctrineBehaviors\Tests\Fixtures\Entity\UserEntity;

final class EntityUserProvider implements UserProviderInterface
{
    private bool $isUserEntityPrepared = false;

    /**
     * @var UserEntity[]
     */
    private array $userEntities = [];

    private UserEntity $userEntity;

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    public function changeUser(string $userName): void
    {
        if ($this->userEntities !== [] && array_key_exists($userName, $this->userEntities)) {
            $this->userEntity = $this->userEntities[$userName];
        } else {
            $errorMessage = sprintf('User with %s name was not found. Add it first.', $userName);
            throw new ShouldNotHappenException($errorMessage);
        }
    }

    public function addUser(string $name, UserEntity $userEntity): void
    {
        $this->userEntities[$name] = $userEntity;
    }

    public function provideUser()
    {
        $this->prepareUserEntities();

        return $this->userEntity;
    }

    public function provideUserEntity(): string
    {
        return UserEntity::class;
    }

    public function prepareUserEntities(): void
    {
        if ($this->isUserEntityPrepared) {
            return;
        }

        $userEntity = new UserEntity(1, 'user');
        $user2Entity = new UserEntity(2, 'user2');

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
