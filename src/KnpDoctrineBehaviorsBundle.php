<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors;

use Knp\DoctrineBehaviors\Bundle\DependencyInjection\KnpDoctrineBehaviorsExtension;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class KnpDoctrineBehaviorsBundle extends Bundle
{
    public function __construct()
    {
        $this->name = 'KnpDoctrineBehaviorsBundle';
    }

    public function getContainerExtension(): Extension
    {
        return new KnpDoctrineBehaviorsExtension();
    }
}
