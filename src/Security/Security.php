<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Security;

use Symfony\Bundle\SecurityBundle\Security as SecurityBundle;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security as SecurityCore;

if (class_exists(SecurityBundle::class)) {
    final class Security {
        public function __construct(
            private SecurityBundle $security,
        ) {
        }

        public function getToken(): ?TokenInterface
        {
            return $this->security->getToken();
        }
    }
} elseif (class_exists(SecurityCore::class)) {
    final class Security {
        public function __construct(
            private SecurityCore $security,
        ) {
        }

        public function getToken(): ?TokenInterface
        {
            return $this->security->getToken();
        }
    }
} else {
    throw new \LogicException(sprintf('You cannot use "%s" because either the Symfony Security Bundle or Symfony Security Core is not installed. If you use Symfony 6.2+, try running "composer require symfony/security-bundle", otherwise run "composer require symfony/security-core".', __CLASS__));
}
