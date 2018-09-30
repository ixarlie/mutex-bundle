<?php

namespace IXarlie\MutexBundle\DependencyInjection\Definition;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Lock\Store\CombinedStore;
use Symfony\Component\Lock\Strategy\ConsensusStrategy;
use Symfony\Component\Lock\Strategy\UnanimousStrategy;

/**
 * Class CombinedDefinition.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class CombinedDefinition extends LockDefinition
{
    /**
     * @inheritdoc
     */
    protected function createStore(ContainerBuilder $container, array $config)
    {
        $combined = new Definition(CombinedStore::class);

        $stores   = [];
        foreach ($config['stores'] as $store) {
            if (preg_match('/(\w+)\.(\w+)/', $store, $matches)) {
                // Service matches type.name
                $store = sprintf('ixarlie_mutex.%s_store.%s', $matches[1], $matches[2]);
            }

            $stores[] = new Reference($store);
        }

        switch ($config['strategy']) {
            case 'consensus':
                $strategy = new Definition(ConsensusStrategy::class);
                break;
            case 'unanimous':
                $strategy = new Definition(UnanimousStrategy::class);
                break;
            default:
                $strategy = new Reference($config['strategy']);
                break;
        }

        $combined
            ->addArgument($stores)
            ->addArgument($strategy)
        ;

        return $combined;
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
                ->arrayNode('stores')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->scalarPrototype()->end()
                ->end()
                ->scalarNode('strategy')->defaultValue('unanimous')->end()
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
        return 'combined';
    }
}
