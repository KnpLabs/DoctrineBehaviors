<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\ORM\Translatable;

use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @author     Florian Klein <florian.klein@free.fr>
 */
class CurrentLocaleCallable
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    public function __invoke(): ?string
    {
        $currentRequest = $this->requestStack->getCurrentRequest();
        if ($currentRequest === null) {
            return null;
        }

        return $currentRequest->getLocale();
    }
}
