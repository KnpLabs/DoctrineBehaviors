<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Contract\Provider;

interface UserProviderInterface
{
    /**
     * @return object|string|null
     */
    public function provideUser();

    public function provideUserEntity(): ?string;
}
