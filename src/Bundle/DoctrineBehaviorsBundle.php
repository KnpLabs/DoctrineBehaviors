<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Bundle;

use Knp\DoctrineBehaviors\Bundle\DependencyInjection\DoctrineBehaviorsExtension;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class DoctrineBehaviorsBundle extends Bundle
{
    public function getContainerExtension(): Extension
    {
        return new DoctrineBehaviorsExtension();
    }
}
