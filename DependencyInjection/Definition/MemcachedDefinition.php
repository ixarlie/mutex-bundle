<?php

namespace IXarlie\MutexBundle\DependencyInjection\Definition;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

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
        $client = new Reference($config['client']);

        $store->addArgument($client);
        $store->addArgument($config['default_ttl']);

        return $store;
    }

    /**
     * @inheritdoc
     */
    public function addConfiguration()
    {
        $tree = new TreeBuilder();
        $node = $tree->root($this->getName());
        $node
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->children()
                ->scalarNode('client')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('default_ttl')->defaultValue(300)->end()
                ->append($this->addBlockConfiguration())
                ->scalarNode('logger')->end()
            ->end()
        ;

        return $node;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'memcached';
    }
}
