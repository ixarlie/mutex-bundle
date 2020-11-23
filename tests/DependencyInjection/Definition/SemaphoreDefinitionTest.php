<?php

namespace Tests\DependencyInjection\Definition;

use IXarlie\MutexBundle\DependencyInjection\Definition\LockDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\SemaphoreDefinition;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Lock\Store\SemaphoreStore;

/**
 * Class SemaphoreDefinitionTest
 */
final class SemaphoreDefinitionTest extends StoreDefinitionTestCase
{
    /**
     * @inheritdoc
     */
    protected function getClassName(): string
    {
        return SemaphoreStore::class;
    }

    /**
     * @inheritdoc
     */
    protected function getDefinitionInstance(): LockDefinition
    {
        return new SemaphoreDefinition();
    }

    /**
     * @inheritdoc
     */
    protected function getDefinitionName(): string
    {
        return 'semaphore';
    }

    /**
     * @inheritdoc
     */
    protected function assertStore(Definition $definition, array $configuration): void
    {
        static::assertCount(0, $definition->getArguments());
        static::assertCount(0, $definition->getMethodCalls());
    }

    /**
     * @inheritdoc
     */
    public function dataDefinitionProvider(): \Generator
    {
        yield [
            [
                'default' => 'foo.bar',
            ],
        ];
        yield [
            [
                'default'     => 'foo.bar',
                'default_ttl' => 500,
            ],
        ];
        yield [
            [
                'default'     => 'foo.bar',
                'default_ttl' => 500,
                'logger'      => 'monolog.logger',
            ],
        ];
        yield [
            [
                'default'     => 'semaphore.default',
                'default_ttl' => 500,
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
                'foo' => null,
                'bar' => null,
            ],
            [
                'foo' => [],
                'bar' => [],
            ],
        ];
        yield [
            [
                'foo' => [
                    'logger' => 'monolog.logger',
                ],
            ],
            [
                'foo' => [
                    'logger' => 'monolog.logger',
                ],
            ],
        ];
    }
}
