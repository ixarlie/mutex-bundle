<?php

namespace IXarlie\MutexBundle\DependencyInjection\Definition;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Lock\Store\MemcachedStore;

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
    public function getName(): string
    {
        return 'memcached';
    }

    /**
     * @inheritdoc
     */
    public function addConfiguration(): NodeDefinition
    {
        $tree = new TreeBuilder($this->getName());
        $node = $tree->getRootNode();
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
    protected function createStore(ContainerBuilder $container, array $config): Definition
    {
        $store  = new Definition(MemcachedStore::class);
        $client = new Reference($config['client']);

        $store->addArgument($client);
        $store->addArgument($config['default_ttl']);

        return $store;
    }
}
