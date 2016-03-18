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
        $treeBuilder->root('ixarlie_mutex')
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('logger')->defaultNull()->end()
                ->arrayNode('flock')
                    ->children()
                        ->scalarNode('cache_dir')->end()
                    ->end()
                ->end()
                ->arrayNode('memcache')
                    ->children()
                        ->scalarNode('client')->end()
                    ->end()
                ->end()
                ->arrayNode('memcached')
                    ->children()
                        ->scalarNode('client')->end()
                    ->end()
                ->end()
                ->arrayNode('redis')
                    ->children()
                        ->scalarNode('client')->end()
                    ->end()
                ->end()
//                ->arrayNode('mysql')
//                    ->children()
//                        ->scalarNode('username')->isRequired()->cannotBeEmpty()->end()
//                        ->scalarNode('password')->isRequired()->cannotBeEmpty()->end()
//                        ->scalarNode('host')->isRequired()->cannotBeEmpty()->end()
//                        ->scalarNode('port')->defaultValue(3306)->end()
//                        ->scalarNode('class_name')->defaultValue('PDO')->end()
//                    ->end()
//                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}