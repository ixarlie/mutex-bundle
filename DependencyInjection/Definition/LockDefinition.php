<?php

namespace IXarlie\MutexBundle\DependencyInjection\Definition;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
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

        // If blocking option is enable, decorates base store with RetryTillSaveStore class
        if (isset($config['blocking'])) {
            $store = new Definition(
                RetryTillSaveStore::class,
                [$store, $config['blocking']['retry_sleep'], $config['blocking']['retry_count']]
            );
        }

        // Register store instance as service
        $store->setPrivate(true);
        $container->setDefinition(sprintf('ixarlie_mutex.%s_store.%s', $this->getName(), $name), $store);

        $factory   = new Definition(Factory::class, [$store]);
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
     * @return string
     */
    abstract public function getName();

    /**
     * @return NodeDefinition
     */
    abstract public function addConfiguration();

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
     * @return NodeDefinition
     */
    final protected function addBlockConfiguration()
    {
        $tree = new TreeBuilder();
        $node = $tree->root('blocking');
        $node
            ->children()
                ->integerNode('retry_sleep')->defaultValue(100)->end()
                ->integerNode('retry_count')->defaultValue(PHP_INT_MAX)->end()
            ->end()
        ;

        return $node;
    }
}
