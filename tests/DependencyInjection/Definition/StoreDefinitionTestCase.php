<?php

namespace Tests\DependencyInjection\Definition;

use IXarlie\MutexBundle\DependencyInjection\Definition\LockDefinition;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\RetryTillSaveStore;

/**
 * Class StoreDefinitionTestCase
 */
abstract class StoreDefinitionTestCase extends TestCase
{
    public function testInstance(): void
    {
        $instance = $this->getDefinitionInstance();

        static::assertInstanceOf(LockDefinition::class, $instance);
    }

    public function testGetName(): void
    {
        $instance = $this->getDefinitionInstance();

        static::assertEquals($this->getDefinitionName(), $instance->getName());
    }

    /**
     * @dataProvider dataDefinitionProvider
     *
     * @param array $configuration
     */
    public function testCreateFactory(array $configuration): void
    {
        $instance  = $this->getDefinitionInstance();
        $container = new ContainerBuilder();
        $name      = 'default';

        $this->preConfigureContainer($container, $name, $configuration);

        $factoryId = sprintf('ixarlie_mutex.%s_factory.%s', $instance->getName(), $name);
        $factory   = $instance->createFactory($container, $configuration, $name);

        // Factory assertions
        static::assertInstanceOf(Definition::class, $factory);
        static::assertEquals(LockFactory::class, $factory->getClass());
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
        static::assertFalse($store->isPublic());
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

        [$storeName, $factoryName] = explode('.', $configuration['default']);
        if ($storeName === $instance->getName() && $factoryName === $name) {
            static::assertTrue($container->hasAlias('ixarlie_mutex.default_factory'));
            static::assertEquals($factoryId, $container->getAlias('ixarlie_mutex.default_factory'));
        } else {
            static::assertFalse($container->hasAlias('ixarlie_mutex_default_factory'));
        }

        static::assertEquals($this->getClassName(), $store->getClass());

        $this->assertStore($store, $configuration);

        // Assert logger injection.
        if (isset($configuration['logger'])) {
            static::assertCount(1, $factory->getMethodCalls());

            $calls = $factory->getMethodCalls();

            [$methodName, $methodArgs] = $calls[0];

            static::assertEquals('setLogger', $methodName);
            static::assertCount(1, $methodArgs);
            static::assertInstanceOf(Reference::class, $methodArgs[0]);
            static::assertEquals($configuration['logger'], (string) $methodArgs[0]);
        } else {
            static::assertCount(0, $factory->getMethodCalls());
        }
    }

    /**
     * @dataProvider dataConfigurationProvider
     *
     * @param array $configuration
     * @param array $expected
     */
    public function testConfiguration(array $configuration, array $expected)
    {
        $tree = new TreeBuilder('i_xarlie_mutex');
        $tree
            ->getRootNode()
            ->append($this->getDefinitionInstance()->addConfiguration())
        ;

        $processor = new Processor();
        $options   = $processor->process(
            $tree->buildTree(),
            [
                'i_xarlie_mutex' => [
                    $this->getDefinitionName() => $configuration,
                ],
            ]
        );

        static::assertEquals($expected, $options[$this->getDefinitionName()]);
    }

    /**
     * @param ContainerBuilder $container
     * @param string           $name
     * @param array            $configuration
     */
    protected function preConfigureContainer(ContainerBuilder $container, string $name, array $configuration): void
    {
        // Implemented by child definitions.
    }

    /**
     * @return string
     */
    abstract protected function getClassName(): string;

    /**
     * @param Definition $definition
     * @param array      $configuration
     */
    abstract protected function assertStore(Definition $definition, array $configuration): void;

    /**
     * @return LockDefinition
     */
    abstract protected function getDefinitionInstance(): LockDefinition;

    /**
     * @return string
     */
    abstract protected function getDefinitionName(): string;

    /**
     * @return \Generator
     */
    abstract public function dataDefinitionProvider(): \Generator;

    /**
     * @return \Generator
     */
    abstract public function dataConfigurationProvider(): \Generator;
}
