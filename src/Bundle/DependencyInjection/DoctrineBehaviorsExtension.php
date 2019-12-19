<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Bundle\DependencyInjection;

use Doctrine\Common\EventSubscriber;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class DoctrineBehaviorsExtension extends Extension
{
    /**
     * @param string[] $configs
     */
    public function load(array $configs, ContainerBuilder $containerBuilder): void
    {
        $loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__ . '/../../../config'));
        $loader->load('services.yaml');

        // @see https://github.com/doctrine/DoctrineBundle/issues/674
        $eventSubscriberAutoconfiguratoin = $this->findEventSubscriberAutoconfiguration($containerBuilder);

        if ($eventSubscriberAutoconfiguratoin) {
            $eventSubscriberAutoconfiguratoin->addTag('doctrine.event_subscriber');
        } else {
            $containerBuilder->registerForAutoconfiguration(EventSubscriber::class)
                ->addTag('doctrine.event_subscriber');
        }
    }

    private function findEventSubscriberAutoconfiguration(ContainerBuilder $containerBuilder): ?ChildDefinition
    {
        foreach ($containerBuilder->getAutoconfiguredInstanceof() as $type => $autoconfiguredInstanceOf) {
            if ($type !== EventSubscriber::class) {
                continue;
            }

            return $autoconfiguredInstanceOf;
        }

        return null;
    }
}
