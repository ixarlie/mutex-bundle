<?php declare(strict_types=1);

/*
 * Copyright (c) 2020, Surex Ltd.
 */

namespace DependencyInjection\Definition;

use IXarlie\MutexBundle\DependencyInjection\Definition\LockDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Lock\Store\ZookeeperStore;

/**
 * Class ZookepperDefinition.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class ZookepperDefinition extends LockDefinition
{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'zookepper';
    }

    /**
     * @inheritDoc
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
                ->append($this->addBlockConfiguration())
                ->scalarNode('logger')->end()
            ->end()
        ;

        return $node;
    }

    /**
     * @inheritDoc
     */
    protected function createStore(ContainerBuilder $container, array $config): Definition
    {
        $store = new Definition(ZookeeperStore::class);
        $store->addArgument($config['client']);

        return $store;
    }
}
