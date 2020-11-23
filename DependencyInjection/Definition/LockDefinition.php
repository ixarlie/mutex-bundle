<?php

namespace IXarlie\MutexBundle\DependencyInjection\Definition;

use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Lock\LockFactory;
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
    final public function createFactory(ContainerBuilder $container, array $config, string $name): Definition
    {
        $default = $config['default'];
        unset($config['default']);

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
        $store->setPublic(false);
        $container->setDefinition(sprintf('ixarlie_mutex.%s_store.%s', $this->getName(), $name), $store);

        $factory   = new Definition(LockFactory::class, [$store]);
        $factoryId = sprintf('ixarlie_mutex.%s_factory.%s', $this->getName(), $name);
        $container->setDefinition($factoryId, $factory);

        if (isset($config['logger'])) {
            // If a logger is configured, add it as argument
            $factory->addMethodCall('setLogger', [new Reference($config['logger'])]);
        }

        [$storeName, $factoryName] = explode('.', $default);

        if ($storeName === $this->getName() && $factoryName === $name) {
            $container->setAlias('ixarlie_mutex.default_factory', $factoryId);
        }

        $factory->addTag('ixarlie_factory', ['type' => $this->getName(), 'name' => $name]);

        return $factory;
    }

    /**
     * @return string
     */
    abstract public function getName(): string;

    /**
     * @return NodeDefinition
     */
    abstract public function addConfiguration(): NodeDefinition;

    /**
     * Create a locker definition that will be use in the LockerManagerInterface
     *
     * @param ContainerBuilder $container
     * @param array            $config
     *
     * @return Definition
     */
    abstract protected function createStore(ContainerBuilder $container, array $config): Definition;

    /**
     * @return NodeDefinition
     */
    final protected function addBlockConfiguration(): NodeDefinition
    {
        $tree = new TreeBuilder('blocking');
        $node = $tree->getRootNode();
        $node
            ->children()
            ->integerNode('retry_sleep')->defaultValue(100)->end()
            ->integerNode('retry_count')->defaultValue(PHP_INT_MAX)->end()
            ->end()
        ;

        return $node;
    }
}
