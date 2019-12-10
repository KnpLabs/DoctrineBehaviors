<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\ORM\Blameable;

use Symfony\Component\DependencyInjection\Container;

final class UserCallable
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param callable $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function __invoke()
    {
        $token = $this->container->get('security.token_storage')->getToken();
        if ($token !== null) {
            return $token->getUser();
        }
    }
}
