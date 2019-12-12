<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Provider;

use Knp\DoctrineBehaviors\Contract\Provider\UserProviderInterface;

final class TestUserProvider implements UserProviderInterface
{
    /**
     * @var mixed User representation
     */
    private $user = 'user';

    /**
     * @var string|null
     */
    private $userEntity;

    public function changeUser($user): void
    {
        $this->user = $user;
    }

    public function provideUser()
    {
        return $this->user;
    }

    public function provideUserEntity(): ?string
    {
        return $this->userEntity;
    }

    public function changeUserEntity(?string $userEntity): void
    {
        $this->userEntity = $userEntity;
    }
}
