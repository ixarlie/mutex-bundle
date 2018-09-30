<?php

namespace IXarlie\MutexBundle\DependencyInjection\Definition;

use IXarlie\MutexBundle\Store\CustomStore;
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
    protected function createStore(ContainerBuilder $container, array $config)
    {
        $store = new Definition(CustomStore::class);
        $store->addArgument(new Reference($config['service']));

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
                ->scalarNode('service')->isRequired()->cannotBeEmpty()->end()
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
        return 'custom';
    }
}
