<?php

namespace IXarlie\MutexBundle\DependencyInjection;

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
        $treeBuilder->root('i_xarlie_mutex')
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('default')->isRequired()->cannotBeEmpty()->end()
                ->arrayNode('flock')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('cache_dir')->end()
                            ->scalarNode('logger')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('memcache')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('host')->end()
                            ->scalarNode('port')->end()
                            ->scalarNode('logger')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('memcached')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('host')->end()
                            ->scalarNode('port')->end()
                            ->scalarNode('logger')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('redis')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('host')->end()
                            ->scalarNode('port')->end()
                            ->scalarNode('password')->end()
                            ->scalarNode('database')->end()
                            ->scalarNode('logger')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('predis')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->arrayNode('connection')
                                ->beforeNormalization()
                                ->ifString()
                                ->then(function ($v) {
                                    return ['uri' => $v];
                                })->end()
                                ->prototype('scalar')->end()
                            ->end()
                            ->arrayNode('options')
                                ->beforeNormalization()
                                ->ifString()
                                ->then(function ($v) {
                                    return ['options' => $v];
                                })->end()
                                ->prototype('scalar')->end()
                            ->end()
                            ->scalarNode('logger')->defaultNull()->end()
                        ->end()
                    ->end()
                ->end()
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
                ->arrayNode('request_listener')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('enabled')->defaultValue(true)->end()
                        ->booleanNode('request_placeholder')->defaultValue(false)->end()
                        ->integerNode('priority')->defaultValue(255)->end()
                        ->integerNode('queue_max_try')
                            ->defaultValue(3)
                            ->validate()
                            ->always()
                            ->then(function ($v) {
                                $v = (int) $v;
                                if ($v < 0) {
                                    throw new InvalidConfigurationException('Value cannot be less than zero in queue_max_try');
                                }
                                return $v;
                            })
                            ->end()                
                        ->end()
                        ->integerNode('queue_timeout')
                            ->defaultValue((int) ini_get('max_execution_time'))
                            ->validate()
                            ->always()
                            ->then(function ($v) {
                                $v = (int) $v;
                                if ($v < 0) {
                                    throw new InvalidConfigurationException('Value cannot be less than zero in queue_timeout');
                                }
                                return $v;
                            })
                            ->end()
                        ->end()
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
