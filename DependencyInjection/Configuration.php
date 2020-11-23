<?php

namespace IXarlie\MutexBundle\DependencyInjection;

use IXarlie\MutexBundle\DependencyInjection\Definition\CombinedDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\CustomDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\FlockDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\MemcachedDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\PdoDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\RedisDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\SemaphoreDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\ZookepperDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
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
     * @inheritdoc
     */
    public function getConfigTreeBuilder()
    {
        $tree = new TreeBuilder('i_xarlie_mutex');
        $node = $tree->getRootNode();
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('default')->isRequired()->cannotBeEmpty()->end()
                ->append((new FlockDefinition())->addConfiguration())
                ->append((new SemaphoreDefinition())->addConfiguration())
                ->append((new RedisDefinition())->addConfiguration())
                ->append((new MemcachedDefinition())->addConfiguration())
                ->append((new CombinedDefinition())->addConfiguration())
                ->append((new PdoDefinition())->addConfiguration())
                ->append((new ZookepperDefinition())->addConfiguration())
                ->append((new CustomDefinition())->addConfiguration())
                ->append($this->addListenerConfiguration())
            ->end()
        ;

        return $tree;
    }

    /**
     * @return NodeDefinition
     */
    private function addListenerConfiguration()
    {
        $tree = new TreeBuilder('request_listener');
        $node = $tree->getRootNode();
        $node
            ->isRequired()
            ->canBeEnabled()
            ->children()
                ->integerNode('priority')->defaultValue(255)->end()
                ->booleanNode('autorelease')->defaultValue(true)->end()
            ->end()
            ->beforeNormalization()
                ->ifNull()
                ->thenEmptyArray()
            ->end()
        ;

        return $node;
    }
}
