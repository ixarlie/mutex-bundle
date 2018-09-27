<?php

namespace IXarlie\MutexBundle\DependencyInjection\Definition;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class RedisDefinition
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class RedisDefinition extends LockDefinition
{
    /**
     * @inheritdoc
     */
    protected function createStore(ContainerBuilder $container, array $config)
    {
        $store  = new Definition('%ixarlie_mutex.redis_store.class%');

        $client = new Definition(\Redis::class);
        $client->addMethodCall('connect', [$config['host'], $config['port']]);
        if (isset($config['password'])) {
            $client->addMethodCall('auth', [$config['password']]);
        }
        if (isset($config['database'])) {
            $client->addMethodCall('select', [(int) $config['database']]);
        }

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
            ->arrayNode('redis')
                ->useAttributeAsKey('name')
                ->prototype('array')
                ->children()
                    ->scalarNode('host')->end()
                    ->scalarNode('port')->end()
                    ->scalarNode('default_ttl')->defaultValue(300)->end()
                    ->scalarNode('password')->end()
                    ->scalarNode('database')->end()
                    ->scalarNode('logger')->defaultNull()->end()
                    ->arrayNode('blocking')
                        ->addDefaultsIfNotSet()
                        ->children()
                            ->integerNode('retry_sleep')->defaultValue(100)->end()
                            ->integerNode('retry_count')->defaultValue(PHP_INT_MAX)->end()
                        ->end()
                    ->end()
                ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @inheritdoc
     */
    protected function getName()
    {
        return 'redis';
    }
}
