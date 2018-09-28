<?php

namespace IXarlie\MutexBundle\DependencyInjection\Definition;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class PRedisDefinition
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class PRedisDefinition extends LockDefinition
{
    /**
     * @inheritdoc
     */
    protected function createStore(ContainerBuilder $container, array $config)
    {
        $store      = new Definition('%ixarlie_mutex.redis_store.class%');
        $client     = new Definition(\Predis\Client::class);
        $parameters = $options = null;

        if (isset($config['connection']['uri'])) {
            $parameters = $config['connection']['uri'];
        } elseif (is_array($config['connection'])) {
            $parameters = $config['connection'];
        }

        if (isset($config['options'])) {
            $options = $config['options'];
        }

        $client->setArguments([$parameters, $options]);

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
            ->arrayNode('predis')
                ->useAttributeAsKey('name')
                ->prototype('array')
                ->children()
                    ->arrayNode('connection')
                    ->beforeNormalization()
                        ->ifString()
                        ->then(function ($v) {
                            return ['uri' => $v];
                        })->end()
                    ->prototype('scalar')->end()
                    ->end()
                    ->arrayNode('options')
                    ->beforeNormalization()
                        ->ifString()
                        ->then(function ($v) {
                            return ['options' => $v];
                        })->end()
                        ->prototype('scalar')->end()
                    ->end()
                    ->scalarNode('default_ttl')->defaultValue(300)->end()
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
    public function getName()
    {
        return 'predis';
    }
}
