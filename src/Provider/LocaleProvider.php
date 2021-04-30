<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Provider;

use Knp\DoctrineBehaviors\Contract\Provider\LocaleProviderInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
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
     * @var ParameterBagInterface
     */
    private $parameterBag;

    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(
        RequestStack $requestStack,
        ParameterBagInterface $parameterBag,
        ?TranslatorInterface $translator
    ) {
        $this->requestStack = $requestStack;
        $this->translator = $translator;
        $this->parameterBag = $parameterBag;
    }

    public function provideCurrentLocale(): ?string
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if ($currentRequest === null) {
            return null;
        }

        $currentLocale = $currentRequest->getLocale();
        if ($currentLocale !== '') {
            return $currentLocale;
        }

        if ($this->translator !== null) {
            return $this->translator->getLocale();
        }

        return null;
    }

    public function provideFallbackLocale(): ?string
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if ($currentRequest !== null) {
            return $currentRequest->getDefaultLocale();
        }

        try {
            if ($this->parameterBag->has('locale')) {
                return $this->parameterBag->get('locale');
            }

            return $this->parameterBag->get('kernel.default_locale');
        } catch (ParameterNotFoundException | InvalidArgumentException $parameterNotFoundException) {
            return null;
        }
    }
}
