<?php

namespace Tests\DependencyInjection\Definition;

use IXarlie\MutexBundle\DependencyInjection\Definition\CustomDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\LockDefinition;
use IXarlie\MutexBundle\Store\CustomStore;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

final class CustomDefinitionTest extends StoreDefinitionTestCase
{
    /**
     * @inheritdoc
     */
    protected function getClassName(): string
    {
        return CustomStore::class;
    }

    /**
     * @inheritdoc
     */
    protected function getDefinitionInstance(): LockDefinition
    {
        return new CustomDefinition();
    }

    /**
     * @inheritdoc
     */
    protected function getDefinitionName(): string
    {
        return 'custom';
    }

    /**
     * @inheritdoc
     */
    protected function preConfigureContainer(ContainerBuilder $container, string $name, array $configuration): void
    {
        $definition = new Definition('%app.service.class%');

        $container->setDefinition($configuration['service'], $definition);
    }

    /**
     * @inheritdoc
     */
    protected function assertStore(Definition $definition, array $configuration): void
    {
        // custom does not have assertions.
        static::assertCount(1, $definition->getArguments());
        static::assertInstanceOf(Reference::class, $definition->getArgument(0));
        static::assertEquals($configuration['service'], (string) $definition->getArgument(0));
    }

    /**
     * @inheritdoc
     */
    public function dataDefinitionProvider(): \Generator
    {
        yield [
            [
                'default' => 'foo.bar',
                'service' => 'my_service',
            ],
        ];
        yield [
            [
                'default' => 'foo.bar',
                'service' => 'my_service',
                'logger'  => 'monolog.logger',
            ],
        ];
        yield [
            [
                'default' => 'flock.default',
                'service' => 'my_service',
                'logger'  => 'monolog.logger',
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function dataConfigurationProvider(): \Generator
    {
        yield [
            [
                'foo' => [
                    'service' => 'my_store_service',
                ],
            ],
            [
                'foo' => [
                    'service' => 'my_store_service',
                ],
            ],
        ];
        yield [
            [
                'foo' => [
                    'service' => 'my_store_service',
                    'logger'  => 'monolog.logger',
                ],
            ],
            [
                'foo' => [
                    'service' => 'my_store_service',
                    'logger'  => 'monolog.logger',
                ],
            ],
        ];
    }
}
