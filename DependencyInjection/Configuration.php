<?php

namespace IXarlie\MutexBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('i_xarlie_mutex')
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('default')->isRequired()->cannotBeEmpty()->end()
                ->arrayNode('flock')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('cache_dir')->end()
                            ->scalarNode('logger')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('memcache')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('host')->end()
                            ->scalarNode('port')->end()
                            ->scalarNode('logger')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('memcached')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('host')->end()
                            ->scalarNode('port')->end()
                            ->scalarNode('logger')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('redis')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('host')->end()
                            ->scalarNode('port')->end()
                            ->scalarNode('logger')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('predis')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('host')->end()
                            ->scalarNode('port')->end()
                            ->scalarNode('logger')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
//                ->arrayNode('mysql')
//                    ->useAttributeAsKey('name')
//                    ->prototype('array')
//                        ->children()
//                            ->scalarNode('username')->isRequired()->cannotBeEmpty()->end()
//                            ->scalarNode('password')->isRequired()->cannotBeEmpty()->end()
//                            ->scalarNode('host')->isRequired()->cannotBeEmpty()->end()
//                            ->scalarNode('port')->defaultValue(3306)->end()
//                            ->scalarNode('class_name')->defaultValue('PDO')->end()
//                            ->scalarNode('logger')->defaultNull()->end()
//                        ->end()
//                    ->end()
//                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
