<?php

namespace IXarlie\MutexBundle\DependencyInjection\Definition;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Lock\Store\SemaphoreStore;

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
    public function getName(): string
    {
        return 'semaphore';
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
        return new Definition(SemaphoreStore::class);
    }
}
