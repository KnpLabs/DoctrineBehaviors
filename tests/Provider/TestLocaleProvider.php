<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Tests\Provider;

use Knp\DoctrineBehaviors\Contract\Provider\LocaleProviderInterface;

final class TestLocaleProvider implements LocaleProviderInterface
{
    public function provideCurrentLocale(): ?string
    {
        return 'en';
    }

    public function provideFallbackLocale(): ?string
    {
        return 'en';
    }
}
