<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Contract\Provider;

interface UserProviderInterface
{
    /**
     * @return mixed|null
     */
    public function provideUser();

    public function provideUserEntity(): ?string;
}
