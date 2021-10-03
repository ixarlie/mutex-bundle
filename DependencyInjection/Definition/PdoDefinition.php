<?php declare(strict_types=1);

/*
 * Copyright (c) 2020, Surex Ltd.
 */

namespace IXarlie\MutexBundle\DependencyInjection\Definition;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Lock\Store\PdoStore;

/**
 * Class PdoDefinition.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class PdoDefinition extends LockDefinition
{
    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'pdo';
    }

    public function addConfiguration(): NodeDefinition
    {
        $tree = new TreeBuilder($this->getName());
        $node = $tree->getRootNode();
        $node
            ->requiresAtLeastOneElement()
            ->useAttributeAsKey('name')
            ->arrayPrototype()
            ->children()
                ->scalarNode('dsn')->isRequired()->cannotBeEmpty()->end()
                ->scalarNode('db_table')->defaultValue('lock_keys')->end()
                ->scalarNode('db_id_col')->defaultValue('key_id')->end()
                ->scalarNode('db_token_col')->defaultValue('key_token')->end()
                ->scalarNode('db_expiration_col')->defaultValue('key_expiration')->end()
                ->arrayNode('db_connection_options')
                    ->useAttributeAsKey('option')
                    ->scalarPrototype()->end()
                ->end()
                ->append($this->addBlockConfiguration())
                ->scalarNode('logger')->end()
            ->end()
        ;

        return $node;
    }

    protected function createStore(ContainerBuilder $container, array $config): Definition
    {
        $store  = new Definition(PdoStore::class);
        $store->addArgument($config['dsn']);

        // Get available options (expect db_username and db_password that should be in the dsn configuration)
        unset($config['dsn'], $config['logger'], $config['blocking']);
        $config = array_filter($config);
        $store->addArgument($config);

        return $store;
    }
}
