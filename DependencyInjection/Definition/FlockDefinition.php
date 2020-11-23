<?php

namespace IXarlie\MutexBundle\DependencyInjection\Definition;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Lock\Store\FlockStore;

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
    public function getName(): string
    {
        return 'flock';
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
                ->scalarNode('lock_dir')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('logger')->end()
            ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * @inheritdoc
     */
    protected function createStore(ContainerBuilder $container, array $config): Definition
    {
        $store = new Definition(FlockStore::class);
        $store->addArgument($config['lock_dir']);

        return $store;
    }
}
