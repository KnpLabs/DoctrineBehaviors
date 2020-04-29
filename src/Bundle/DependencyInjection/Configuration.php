<?php

declare(strict_types=1);

namespace Knp\DoctrineBehaviors\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('doctrine_behaviors');

        $treeBuilder->getRootNode()
            ->children()
                ->arrayNode('translatable')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->enumNode('translatable_fetch_mode')
                            ->info('The doctrine fetch mode for translatable.')
                            ->values(['LAZY', 'EAGER', 'EXTRA_LAZY'])
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue('LAZY')
                        ->end()
                        ->enumNode('translation_fetch_mode')
                            ->info('The doctrine fetch mode for translation.')
                            ->values(['LAZY', 'EAGER', 'EXTRA_LAZY'])
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue('LAZY')
                        ->end()
                    ->end()
                ->end() // Translatable
                ->arrayNode('blameable')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('user_entity')
                            ->info('The user entity class used by blameable.')
                            ->defaultNull()
                        ->end()
                    ->end()
                ->end() // Blameable
                ->arrayNode('timestampable')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->enumNode('date_field_type')
                            ->info('The doctrine field type for timestampable fields.')
                            ->values(['datetime', 'datetimetz'])
                            ->isRequired()
                            ->cannotBeEmpty()
                            ->defaultValue('datetime')
                        ->end()
                    ->end()
                ->end() // Timestampable
            ->end()
        ;

        return $treeBuilder;
    }
}
