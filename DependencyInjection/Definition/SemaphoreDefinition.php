<?php

namespace IXarlie\MutexBundle\DependencyInjection\Definition;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class SemaphoreDefinition.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class SemaphoreDefinition extends LockDefinition
{

    /**
     * @inheritdoc
     */
    protected function createStore(ContainerBuilder $container, array $config)
    {
        $store = new Definition('%ixarlie_mutex.semaphore_store.class%');

        return $store;
    }

    /**
     * @inheritdoc
     */
    public static function addConfiguration(NodeBuilder $nodeBuilder)
    {
        return $nodeBuilder
            ->arrayNode('semaphore')
                ->useAttributeAsKey('name')
                ->prototype('array')
                ->children()
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
        return 'semaphore';
    }
}
