<?php

namespace Tests\DependencyInjection\Definition;

use IXarlie\MutexBundle\DependencyInjection\Definition\LockDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\RedisDefinition;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Lock\Store\RedisStore;

/**
 * Class RedisDefinitionTest
 */
final class RedisDefinitionTest extends StoreDefinitionTestCase
{
    /**
     * @inheritdoc
     */
    protected function getClassName(): string
    {
        return RedisStore::class;
    }

    /**
     * @inheritdoc
     */
    protected function getDefinitionInstance(): LockDefinition
    {
        return new RedisDefinition();
    }

    /**
     * @inheritdoc
     */
    protected function getDefinitionName(): string
    {
        return 'redis';
    }

    /**
     * @inheritdoc
     */
    protected function assertStore(Definition $definition, array $configuration): void
    {
        static::assertCount(2, $definition->getArguments());

        /** @var Definition $client */
        $client = $definition->getArgument(0);
        static::assertInstanceOf(Reference::class, $client);
        static::assertEquals($configuration['client'], (string) $client);

        static::assertEquals($configuration['default_ttl'], $definition->getArgument(1));
    }

    /**
     * @inheritdoc
     */
    public function dataDefinitionProvider(): \Generator
    {
        yield [
            [
                'default'     => 'foo.bar',
                'client'      => 'redis_client',
                'default_ttl' => 500,
            ],
        ];
        yield [
            [
                'default'     => 'foo.bar',
                'client'      => 'redis_client',
                'default_ttl' => 500,
                'logger'      => 'monolog.logger',
            ],
        ];
        yield [
            [
                'default'     => 'redis.default',
                'client'      => 'redis_client',
                'default_ttl' => 500,
            ],
        ];
        yield [
            [
                'default'     => 'redis.default',
                'client'      => 'redis_client',
                'default_ttl' => 500,
                'blocking'    => [
                    'retry_count' => 500,
                    'retry_sleep' => 1000,
                ],
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
                    'client' => 'redis_client',
                ],
            ],
            [
                'foo' => [
                    'client'      => 'redis_client',
                    'default_ttl' => 300,
                ],
            ],
        ];
        yield [
            [
                'foo' => [
                    'client'      => 'redis_client',
                    'default_ttl' => 500,
                    'logger'      => 'monolog.logger',
                ],
            ],
            [
                'foo' => [
                    'client'      => 'redis_client',
                    'default_ttl' => 500,
                    'logger'      => 'monolog.logger',
                ],
            ],
        ];
        yield [
            [
                'foo' => [
                    'client'      => 'redis_client',
                    'default_ttl' => 500,
                    'blocking'    => [],
                ],
            ],
            [
                'foo' => [
                    'client'      => 'redis_client',
                    'default_ttl' => 500,
                    'blocking'    => [
                        'retry_sleep' => 100,
                        'retry_count' => PHP_INT_MAX,
                    ],
                ],
            ],
        ];
        yield [
            [
                'foo' => [
                    'client'      => 'redis_client',
                    'default_ttl' => 500,
                    'blocking'    => [
                        'retry_sleep' => 200,
                        'retry_count' => 3,
                    ],
                ],
            ],
            [
                'foo' => [
                    'client'      => 'redis_client',
                    'default_ttl' => 500,
                    'blocking'    => [
                        'retry_sleep' => 200,
                        'retry_count' => 3,
                    ],
                ],
            ],
        ];
    }
}
