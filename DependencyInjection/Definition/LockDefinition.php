<?php

namespace IXarlie\MutexBundle\DependencyInjection\Definition;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\RetryTillSaveStore;

/**
 * Class LockDefinition
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
abstract class LockDefinition
{
    /**
     * @param ContainerBuilder $container
     * @param array            $config
     *
     * @return Definition
     */
    public function createFactory(ContainerBuilder $container, array $config)
    {
        // Get the store definition.
        $store = $this->createStore($container, $config);

        // Decorates base store with RetryTillSaveStore class
        if (isset($config['blocking'])) {
            $blockStore = new Definition(RetryTillSaveStore::class);
            $blockStore
                ->addArgument($store)
                ->addArgument($config['blocking']['retry_sleep'])
                ->addArgument($config['blocking']['retry_count'])
            ;

            $store = $blockStore;
        }

        $factory = new Definition(Factory::class);
        $factory->addArgument($store);

        // LockerManager first argument is a \NinjaMutex\Lock\LockInterface definition.
        if (isset($config['logger'])) {
            // If a logger is configured, add it as argument
            $factory->addMethodCall('setLogger', [new Reference($config['logger'])]);
        }

        return $factory;
    }

    /**
     * Create a locker definition that will be use in the LockerManagerInterface
     *
     * @param ContainerBuilder $container
     * @param array            $config
     *
     * @return Definition
     */
    abstract protected function createStore(ContainerBuilder $container, array $config);

    /**
     * @param NodeBuilder $nodeBuilder
     *
     * @return NodeBuilder
     */
    abstract public static function addConfiguration(NodeBuilder $nodeBuilder);
}
