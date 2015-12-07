<?php

namespace Knp\DoctrineBehaviors\Bundle;

use Symfony\Component\DependencyInjection\ContainerBuilder;

use Knp\DoctrineBehaviors\Bundle\DependencyInjection\DoctrineBehaviorsExtension;
use Symfony\Component\HttpKernel\Bundle\Bundle;

use Knp\DoctrineBehaviors\Bundle\DependencyInjection\Compiler;

class DoctrineBehaviorsBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new Compiler\TrackerRegistrationCompilerPass());
    }

    public function getContainerExtension()
    {
        return new DoctrineBehaviorsExtension();
    }
}
