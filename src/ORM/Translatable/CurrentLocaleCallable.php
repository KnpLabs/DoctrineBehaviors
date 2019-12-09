<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\ORM\Translatable;

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author     Florian Klein <florian.klein@free.fr>
 */
class CurrentLocaleCallable
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function __invoke()
    {
        if (!$this->container->has('request_stack')) {
            if (!$this->container->isScopeActive('request')) {
                return null;
            }
            $request = $this->container->get('request');

            return $request->getLocale();
        } elseif ($request = $this->container->get('request_stack')->getCurrentRequest()) {
            return $request->getLocale();
        }

        return null;
    }
}
