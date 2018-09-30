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
        $stores = [];
        foreach ($config['stores'] as $store) {
            $stores[] = new Reference($store);
        }

        $strategy = $this->createStrategy($config['strategy']);
        $combined = new Definition(CombinedStore::class, [$stores, $strategy]);

        return $combined;
    }

    /**
     * @param string $strategy
     *
     * @return Definition|Reference
     */
    private function createStrategy($strategy)
    {
        switch ($strategy) {
            case 'consensus':
                $strategy = new Definition(ConsensusStrategy::class);
                break;
            case 'unanimous':
                $strategy = new Definition(UnanimousStrategy::class);
                break;
            default:
                $strategy = new Reference($strategy);
                break;
        }

        return $strategy;
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
                    ->scalarPrototype()
                        ->beforeNormalization()
                            ->ifTrue(function ($v) {
                                return preg_match('/^(\w+)\.(\w+)$/', $v);
                            })
                            ->then(function ($v) {
                                $parts = explode('.', $v);

                                return sprintf('ixarlie_mutex.%s_store.%s', $parts[0], $parts[1]);
                            })
                        ->end()
                    ->end()
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
