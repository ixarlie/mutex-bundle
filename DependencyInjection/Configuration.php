<?php

namespace IXarlie\MutexBundle\DependencyInjection;

use IXarlie\MutexBundle\DependencyInjection\Definition\FlockDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\MemcachedDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\PRedisDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\RedisDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $nodeBuilder = $treeBuilder->root('ixarlie_mutex')
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('default')->isRequired()->cannotBeEmpty()->end()
        ;

        $nodeBuilder = FlockDefinition::addConfiguration($nodeBuilder);
        $nodeBuilder = MemcachedDefinition::addConfiguration($nodeBuilder);
        $nodeBuilder = RedisDefinition::addConfiguration($nodeBuilder);
        $nodeBuilder = PRedisDefinition::addConfiguration($nodeBuilder);

        // RequestListener configuration
        $nodeBuilder
            ->arrayNode('request_listener')
            ->addDefaultsIfNotSet()
            ->children()
                ->booleanNode('enabled')->defaultValue(true)->end()
                ->booleanNode('request_placeholder')->defaultValue(false)->end()
                ->integerNode('priority')->defaultValue(255)->end()
                ->booleanNode('translator')->end()
                ->booleanNode('user_isolation')->end()
                ->arrayNode('http_exception')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('message')->defaultValue('Resource is not available at this moment')->end()
                        ->integerNode('code')->defaultValue(409)->end()
                    ->end()
                ->end()
            ->end()
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
