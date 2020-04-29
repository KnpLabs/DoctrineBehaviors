<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Bundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

final class KnpDoctrineBehaviorsExtension extends Extension
{
    /**
     * @param string[] $configs
     */
    public function load(array $configs, ContainerBuilder $containerBuilder): void
    {
        $loader = new YamlFileLoader($containerBuilder, new FileLocator(dirname(__DIR__) . '/../Resources/config'));
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $this->loadProviders($config, $containerBuilder);
        $this->loadSubscribers($config, $containerBuilder);
    }

    private function loadProviders(array $config, ContainerBuilder $containerBuilder): void
    {
        $definition = $containerBuilder->getDefinition('knp_doctrine_behaviors.user_provider');
        $definition->replaceArgument(1, $config['blameable']['user_entity']);
    }

    private function loadSubscribers(array $config, ContainerBuilder $containerBuilder): void
    {
        $definition = $containerBuilder->getDefinition('knp_doctrine_behaviors.event_subscriber.blameable');
        $definition->replaceArgument(2, $config['blameable']['user_entity']);

        $definition = $containerBuilder->getDefinition('knp_doctrine_behaviors.event_subscriber.timestampable');
        $definition->replaceArgument(0, $config['timestampable']['date_field_type']);

        $definition = $containerBuilder->getDefinition('knp_doctrine_behaviors.event_subscriber.translatable');
        $definition->replaceArgument(1, $config['translatable']['translatable_fetch_mode']);
        $definition->replaceArgument(2, $config['translatable']['translation_fetch_mode']);
    }
}
