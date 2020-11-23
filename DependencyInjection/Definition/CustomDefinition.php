<?php

namespace IXarlie\MutexBundle\DependencyInjection\Definition;

use IXarlie\MutexBundle\Store\CustomStore;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class CustomDefinition.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class CustomDefinition extends LockDefinition
{
    /**
     * @inheritdoc
     */
    public function getName(): string
    {
        return 'custom';
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
                ->scalarNode('service')->isRequired()->cannotBeEmpty()->end()
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
        $store = new Definition(CustomStore::class);
        $store->addArgument(new Reference($config['service']));

        return $store;
    }
}
