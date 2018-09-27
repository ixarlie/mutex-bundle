<?php

namespace IXarlie\MutexBundle\DependencyInjection\Definition;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class MemcachedDefinition
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class MemcachedDefinition extends LockDefinition
{
    /**
     * @inheritdoc
     */
    protected function createStore(ContainerBuilder $container, array $config)
    {
        $store  = new Definition('%ixarlie_mutex.memcached_store.class%');

        $client = new Definition('\Memcached');
        $client->addMethodCall('addServer', [$config['host'], $config['port']]);

        $store->addArgument($client);
        $store->addArgument($config['default_ttl']);

        return $store;
    }

    /**
     * @inheritdoc
     */
    public static function addConfiguration(NodeBuilder $nodeBuilder)
    {
        return $nodeBuilder
            ->arrayNode('memcached')
                ->useAttributeAsKey('name')
                ->prototype('array')
                ->children()
                    ->scalarNode('host')->end()
                    ->scalarNode('port')->end()
                    ->scalarNode('default_ttl')->defaultValue(300)->end()
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
