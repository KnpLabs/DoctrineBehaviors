<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Provider;

use Knp\DoctrineBehaviors\Contract\Provider\UserProviderInterface;
use Symfony\Component\Security\Core\Security;

final class UserProvider implements UserProviderInterface
{
    private ?string $blameableUserEntity;

    private Security $security;

    public function __construct(Security $security, ?string $blameableUserEntity = null)
    {
        $this->security = $security;
        $this->blameableUserEntity = $blameableUserEntity;
    }

    public function provideUser()
    {
        $token = $this->security->getToken();
        if ($token !== null) {
            $user = $token->getUser();
            if ($this->blameableUserEntity) {
                if ($user instanceof $this->blameableUserEntity) {
                    return $user;
                }
            } else {
                return $user;
            }
        }

        return null;
    }

    public function provideUserEntity(): ?string
    {
        $user = $this->provideUser();
        if ($user === null) {
            return null;
        }

        if (is_object($user)) {
            return get_class($user);
        }

        return null;
    }
}
