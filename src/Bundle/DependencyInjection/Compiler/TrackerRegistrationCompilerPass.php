<?php
namespace Knp\DoctrineBehaviors\Bundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class TrackerRegistrationCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('knp.doctrine_behaviors.trackable_subscriber')) {
            return;
        }

        $definition = $container->getDefinition('knp.doctrine_behaviors.trackable_subscriber');

        $taggedServices = $container->findTaggedServiceIds('knp.doctrine_behaviors.tracker');
        foreach ($taggedServices as $id => $_) {
            $definition->addMethodCall('addTracker', [new Reference($id)]);
        }
    }
}
