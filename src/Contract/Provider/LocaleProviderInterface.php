<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Contract\Provider;

interface LocaleProviderInterface
{
    public function provideCurrentLocale(): ?string;

    public function provideFallbackLocale(): ?string;
}
