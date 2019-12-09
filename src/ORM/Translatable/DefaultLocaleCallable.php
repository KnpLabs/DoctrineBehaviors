<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\ORM\Translatable;

use Symfony\Component\DependencyInjection\Container;

/**
 * @author    Jérôme Fix <jerome.fix@zapoyok.info>
 */
class DefaultLocaleCallable
{
    private $locale;

    public function __construct($locale)
    {
        $this->locale = $locale;
    }

    public function __invoke()
    {
        return $this->locale;
    }
}
