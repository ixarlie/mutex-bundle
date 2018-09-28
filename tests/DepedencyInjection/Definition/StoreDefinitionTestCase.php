<?php

namespace Tests\DepedencyInjection\Definition;

use IXarlie\MutexBundle\DependencyInjection\Definition\LockDefinition;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\RetryTillSaveStore;

/**
 * Class StoreDefinitionTestCase
 */
abstract class StoreDefinitionTestCase extends TestCase
{
    public function testInstance()
    {
        $instance = $this->getDefinitionInstance();

        static::assertInstanceOf(LockDefinition::class, $instance);
    }

    public function testGetName()
    {
        $instance = $this->getDefinitionInstance();

        static::assertEquals($this->getDefinitionName(), $instance->getName());
    }

    /**
     * @dataProvider getConfigurationProvider
     *
     * @param array  $configuration
     * @param string $storeClass
     * @param string $strategyClass
     */
    public function testCreateFactory(array $configuration, $storeClass, $options)
    {
        $instance  = $this->getDefinitionInstance();
        $container = new ContainerBuilder();
        $name      = 'default';

        $factoryId = sprintf('ixarlie_mutex.%s_factory.%s', $instance->getName(), $name);
        $factory   = $instance->createFactory($container, $configuration, $name);

        // Factory assertions
        static::assertInstanceOf(Definition::class, $factory);
        static::assertEquals(Factory::class, $factory->getClass());
        static::assertCount(1, $factory->getArguments());
        static::assertCount(1, $factory->getTags());
        static::assertCount(1, $factory->getTag('ixarlie_factory'));

        $tags = $factory->getTag('ixarlie_factory');
        static::assertEquals(['type' => $instance->getName(), 'name' => $name], $tags[0]);
        static::assertTrue($container->hasDefinition($factoryId));
        static::assertSame($factory, $container->getDefinition($factoryId));

        // Store assertions
        /** @var Definition $store */
        $storeId = sprintf('ixarlie_mutex.%s_store.%s', $instance->getName(), $name);
        $store   = $factory->getArgument(0);

        static::assertInstanceOf(Definition::class, $store);
        static::assertTrue($store->isPrivate());
        static::assertTrue($container->hasDefinition($storeId));
        static::assertSame($store, $container->getDefinition($storeId));

        if (isset($configuration['blocking'])) {
            static::assertEquals(RetryTillSaveStore::class, $store->getClass());
            static::assertEquals($configuration['blocking']['retry_sleep'], $store->getArgument(1));
            static::assertEquals($configuration['blocking']['retry_count'], $store->getArgument(2));

            // The decorated store
            /** @var Definition $store */
            $store = $store->getArgument(0);
        }

        list($storeName, $factoryName) = explode('.', $configuration['default']);
        if ($storeName === $instance->getName() && $factoryName === $name) {
            static::assertTrue($container->hasAlias('ixarlie_mutex_default_factory'));
            static::assertEquals($factoryId, $container->getAlias());
        } else {
            static::assertFalse($container->hasAlias('ixarlie_mutex_default_factory'));
        }

        static::assertEquals($storeClass, $store->getClass());

        $this->assertStore($store, $configuration);

        // Assert logger injection.
        if (isset($configuration['logger'])) {
            static::assertCount(1, $factory->getMethodCalls());

            $calls = $factory->getMethodCalls();

            list($methodName, $methodArgs) = $calls[0];

            static::assertEquals('setLogger', $methodName);
            static::assertCount(1, $methodArgs);
            static::assertInstanceOf(Reference::class, $methodArgs[0]);
            static::assertEquals($configuration['logger'], $methodArgs[0]);
        } else {
            static::assertCount(0, $factory->getMethodCalls());
        }
    }

    /**
     * @return array
     */
    abstract public function getConfigurationProvider();

    /**
     * @param Definition $definition
     * @param array      $configuration
     */
    abstract protected function assertStore(Definition $definition, array $configuration);

    /**
     * @return LockDefinition
     */
    abstract protected function getDefinitionInstance();

    /**
     * @return string
     */
    abstract protected function getDefinitionName();
}
