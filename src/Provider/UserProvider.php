<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Provider;

use Knp\DoctrineBehaviors\Contract\Provider\UserProviderInterface;
use Symfony\Component\Security\Core\Security;

final class UserProvider implements UserProviderInterface
{
    /**
     * @var Security
     */
    private $security;

    public function __construct(Security $security)
    {
        $this->security = $security;
    }

    public function provideUser()
    {
        $token = $this->security->getToken();
        if ($token !== null) {
            return $token->getUser();
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
