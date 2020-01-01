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
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(RequestStack $requestStack, ?TranslatorInterface $translator)
    {
        $this->requestStack = $requestStack;
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

        return $this->getTranslatorLocale();
    }

    public function provideFallbackLocale(): ?string
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if ($currentRequest === null) {
            return null;
        }

        $defaultLocale = $currentRequest->getDefaultLocale();
        if ($defaultLocale) {
            return $defaultLocale;
        }

        return $this->getTranslatorLocale();
    }

    private function getTranslatorLocale(): ?string
    {
        if ($this->translator) {
            return $this->translator->getLocale();
        }

        return null;
    }
}
