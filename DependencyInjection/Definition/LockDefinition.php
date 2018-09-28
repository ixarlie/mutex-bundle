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
     * @param string           $name
     *
     * @return Definition
     */
    public function createFactory(ContainerBuilder $container, array $config, $name)
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

        // Register store instance as service
        $storeId = sprintf('ixarlie_mutex.%s_store.%s', $this->getName(), $name);
        $store->setPrivate(true);
        $container->setDefinition($storeId, $store);

        $factory = new Definition(Factory::class);
        $factory->addArgument($store);

        $factoryId = sprintf('ixarlie_mutex.%s_factory.%s', $this->getName(), $name);
        $container->setDefinition($factoryId, $factory);

        // LockerManager first argument is a \NinjaMutex\Lock\LockInterface definition.
        if (isset($config['logger'])) {
            // If a logger is configured, add it as argument
            $factory->addMethodCall('setLogger', [new Reference($config['logger'])]);
        }

        list($storeName, $factoryName) = explode('.', $config['default']);

        if ($storeName === $this->getName() && $factoryName === $name) {
            $container->setAlias('ixarlie_mutex.default_factory', $factoryId);
        }

        $factory->addTag('ixarlie_factory', ['type' => $this->getName(), 'name' => $name]);

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
     * @return string
     */
    abstract public function getName();

    /**
     * @param NodeBuilder $nodeBuilder
     *
     * @return NodeBuilder
     */
    public static function addConfiguration(NodeBuilder $nodeBuilder)
    {
        throw new \RuntimeException('Configuration must be implemented');
    }
}
