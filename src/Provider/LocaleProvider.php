<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Provider;

use Knp\DoctrineBehaviors\Contract\Provider\LocaleProviderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class LocaleProvider implements LocaleProviderInterface
{
    /**
     * @var TranslatorInterface&LocaleAwareInterface|null
     */
    private $translator;

    /**
     * @var string|null
     */
    private $defaultLocale;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(RequestStack $requestStack, string $defaultLocale, ?TranslatorInterface $translator)
    {
        $this->requestStack = $requestStack;
        $this->defaultLocale = $defaultLocale;
        $this->translator = $translator;
    }

    public function provideCurrentLocale(): ?string
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if ($currentRequest === null) {
            return null;
        }

        $currentLocale = $currentRequest->getLocale();
        if ($currentLocale) {
            return $currentLocale;
        }

        if ($this->translator) {
            return $this->translator->getLocale();
        }

        return null;
    }

    public function provideFallbackLocale(): ?string
    {
        return $this->defaultLocale;
    }
}
