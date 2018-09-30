<?php

namespace IXarlie\MutexBundle\DependencyInjection\Definition;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

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
    protected function createStore(ContainerBuilder $container, array $config)
    {
        $store = new Definition('%ixarlie_mutex.flock_store.class%');
        $store->addArgument($config['lock_dir']);

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
                ->scalarNode('lock_dir')->isRequired()->cannotBeEmpty()->end()
                ->append($this->addBlockConfiguration())
                ->scalarNode('logger')->end()
            ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'flock';
    }
}
