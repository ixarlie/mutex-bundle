<?php

namespace IXarlie\MutexBundle\DependencyInjection;

use IXarlie\MutexBundle\DependencyInjection\Definition\FlockDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\MemcachedDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\PRedisDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\RedisDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

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
        $nodeBuilder = $treeBuilder->root('i_xarlie_mutex')
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

//                ->arrayNode('memcache')
//                    ->useAttributeAsKey('name')
//                    ->prototype('array')
//                        ->children()
//                            ->scalarNode('host')->end()
//                            ->scalarNode('port')->end()
//                            ->scalarNode('logger')->defaultNull()->end()
//                        ->end()
//                    ->end()
//                ->end()
//                ->arrayNode('mysql')
//                    ->useAttributeAsKey('name')
//                    ->prototype('array')
//                        ->children()
//                            ->scalarNode('username')->isRequired()->cannotBeEmpty()->end()
//                            ->scalarNode('password')->isRequired()->cannotBeEmpty()->end()
//                            ->scalarNode('host')->isRequired()->cannotBeEmpty()->end()
//                            ->scalarNode('port')->defaultValue(3306)->end()
//                            ->scalarNode('class_name')->defaultValue('PDO')->end()
//                            ->scalarNode('logger')->defaultNull()->end()
//                        ->end()
//                    ->end()
//                ->end()
}
