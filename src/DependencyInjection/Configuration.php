<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\DependencyInjection;

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
     * @inheritDoc
     */
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder('i_xarlie_mutex');

        $builder
            ->getRootNode()
            ->children()
                ->arrayNode('factories')
                    ->defaultValue([])
                    ->scalarPrototype()->end()
                ->end()
            ->end()
        ;

        return $builder;
    }
}
