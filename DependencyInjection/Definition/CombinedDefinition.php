<?php

namespace IXarlie\MutexBundle\DependencyInjection\Definition;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
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
            $stores[] = new Reference($store);
        }

        switch ($config['strategy']) {
            case 'consensus':
                $strategy = new ConsensusStrategy();
                break;
            case 'unanimous':
                $strategy = new UnanimousStrategy();
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
    public static function addConfiguration(NodeBuilder $nodeBuilder)
    {
        return $nodeBuilder
            ->arrayNode('combined')
                ->useAttributeAsKey('name')
                ->prototype('array')
                ->children()
                    ->arrayNode('stores')->isRequired()
                        ->validate()
                            ->ifEmpty()
                            ->thenInvalid('At least one store is mandatory.')
                        ->end()
                    ->end()
                    ->arrayNode('strategy')->defaultValue('unanimous')->end()
                    ->scalarNode('logger')->defaultNull()->end()
                    ->arrayNode('blocking')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->integerNode('retry_sleep')->defaultValue(100)->end()
                        ->integerNode('retry_count')->defaultValue(PHP_INT_MAX)->end()
                    ->end()
                ->end()
                ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @inheritdoc
     */
    protected function getName()
    {
        return 'combined';
    }
}
