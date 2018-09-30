<?php

namespace IXarlie\MutexBundle\DependencyInjection\Definition;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
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
    public function addConfiguration()
    {
        $tree = new TreeBuilder();
        $node = $tree->root($this->getName());
        $node
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->children()
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
        return 'semaphore';
    }
}
