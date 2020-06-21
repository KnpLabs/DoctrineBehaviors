<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Bundle\DependencyInjection;

use Doctrine\Common\EventSubscriber;
use Roukmoute\HashidsBundle\RoukmouteHashidsBundle;
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

        $this->loadHasihds($containerBuilder, $loader);

        // @see https://github.com/doctrine/DoctrineBundle/issues/674
        $containerBuilder->registerForAutoconfiguration(EventSubscriber::class)
            ->addTag(self::DOCTRINE_EVENT_SUBSCRIBER_TAG);
    }

    /**
     * Enables hashids only if the bundle is registered
     */
    private function loadHasihds(ContainerBuilder $containerBuilder, YamlFileLoader $loader): void
    {
        if (! $containerBuilder->hasParameter('kernel.bundles')) {
            return;
        }

        foreach ($containerBuilder->getParameter('kernel.bundles') as $bundle) {
            if ($bundle !== RoukmouteHashidsBundle::class) {
                continue;
            }

            $loader->load('behavior/hashids.yaml');
        }
    }
}
