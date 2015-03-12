<?php

namespace Knp\DoctrineBehaviors\ORM\Translatable;

use Symfony\Component\DependencyInjection\Container;

/**
 * @author    Jérôme Fix <jerome.fix@zapoyok.info>
 */
class DefaultLocaleCallable
{
    private $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function __invoke()
    {
        if (!$this->container->hasParameter('locale')) {
            return 'en';
        }

        return $this->container->getParameter('locale');
    }
}
