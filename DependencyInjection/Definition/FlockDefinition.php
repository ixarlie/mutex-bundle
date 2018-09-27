<?php

namespace IXarlie\MutexBundle\DependencyInjection\Definition;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class FlockDefinition
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class FlockDefinition extends LockDefinition
{
    /**
     * @inheritdoc
     */
    protected function createStore(ContainerBuilder $container, array $config)
    {
        $store = new Definition('%ixarlie_mutex.flock_store.class%');
        $store->addArgument($config['cache_dir']);

        return $store;
    }

    /**
     * @inheritdoc
     */
    public static function addConfiguration(NodeBuilder $nodeBuilder)
    {
        return $nodeBuilder
            ->arrayNode('flock')
                ->useAttributeAsKey('name')
                ->prototype('array')
                ->children()
                    ->scalarNode('cache_dir')->end()
                    ->scalarNode('logger')->defaultNull()->end()
                    ->arrayNode('blocking')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->scalarNode('retry_sleep')->defaultValue(100)->end()
                            ->integerNode('retry_count')->defaultValue(PHP_INT_MAX)->end()
                        ->end()
                    ->end()
                ->end()
                ->end()
            ->end()
        ;
    }
}
