<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Validation;

use Knp\DoctrineBehaviors\Contract\Provider\UserProviderInterface;

final class BlameableUserValidation
{
    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    public function isUserValid($user): bool
    {
        $userEntity = $this->userProvider->provideUserEntity();

        // is expected user entity type?
        if ($userEntity !== null) {
            return is_a($user, $userEntity);
        }

        // is object that can be converted to string?
        if (is_object($user)) {
            return method_exists($user, '__toString');
        }

        // is string?
        return is_string($user);
    }
}
