<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Bundle\DependencyInjection;

use Knp\DoctrineBehaviors\EventSubscriber\EventSubscriberInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class DoctrineBehaviorsExtension extends Extension
{
    /**
     * @var string
     */
    private const DOCTRINE_EVENT_SUBSCRIBER_TAG = 'doctrine.event_subscriber';

    /**
     * @param string[] $configs
     */
    public function load(array $configs, ContainerBuilder $containerBuilder): void
    {
        $loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__ . '/../../../config'));
        $loader->load('services.yaml');

        // @see https://github.com/doctrine/DoctrineBundle/issues/674
        $containerBuilder->registerForAutoconfiguration(EventSubscriberInterface::class)
            ->addTag(self::DOCTRINE_EVENT_SUBSCRIBER_TAG);
    }
}
