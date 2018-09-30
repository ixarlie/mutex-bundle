<?php

namespace Tests\DependencyInjection\Definition;

use IXarlie\MutexBundle\DependencyInjection\Definition\RedisDefinition;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class RedisDefinitionTest
 */
class RedisDefinitionTest extends StoreDefinitionTestCase
{
    /**
     * @inheritdoc
     */
    protected function getClassName()
    {
        return '%ixarlie_mutex.redis_store.class%';
    }

    /**
     * @inheritdoc
     */
    protected function getDefinitionInstance()
    {
        return new RedisDefinition();
    }

    /**
     * @inheritdoc
     */
    protected function getDefinitionName()
    {
        return 'redis';
    }

    /**
     * @inheritdoc
     */
    protected function assertStore(Definition $definition, array $configuration)
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
    public function getDefinitionProvider()
    {
        yield [
            [
                'default'     => 'foo.bar',
                'client'      => 'redis_client',
                'default_ttl' => 500,
            ]
        ];
        yield [
            [
                'default'     => 'foo.bar',
                'client'      => 'redis_client',
                'default_ttl' => 500,
                'logger'      => 'monolog.logger'
            ]
        ];
        yield [
            [
                'default'     => 'redis.default',
                'client'      => 'redis_client',
                'default_ttl' => 500,
            ]
        ];
        yield [
            [
                'default'     => 'redis.default',
                'client'      => 'redis_client',
                'default_ttl' => 500,
                'blocking'    => [
                    'retry_count' => 500,
                    'retry_sleep' => 1000,
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public function getConfigurationProvider()
    {
        yield [
            [
                'foo' => [
                    'client' => 'redis_client',
                ]
            ],
            [
                'foo' => [
                    'client'      => 'redis_client',
                    'default_ttl' => 300,
                ]
            ]
        ];
        yield [
            [
                'foo' => [
                    'client'      => 'redis_client',
                    'default_ttl' => 500,
                ]
            ],
            [
                'foo' => [
                    'client'      => 'redis_client',
                    'default_ttl' => 500,
                ]
            ]
        ];
        yield [
            [
                'foo' => [
                    'client'      => 'redis_client',
                    'default_ttl' => 500,
                    'blocking'    => [],
                ]
            ],
            [
                'foo' => [
                    'client'      => 'redis_client',
                    'default_ttl' => 500,
                    'blocking'    => [
                        'retry_sleep' => 100,
                        'retry_count' => PHP_INT_MAX
                    ],
                ]
            ]
        ];
        yield [
            [
                'foo' => [
                    'client'      => 'redis_client',
                    'default_ttl' => 500,
                    'blocking'    => [
                        'retry_sleep' => 200,
                        'retry_count' => 3
                    ],
                ]
            ],
            [
                'foo' => [
                    'client'      => 'redis_client',
                    'default_ttl' => 500,
                    'blocking'    => [
                        'retry_sleep' => 200,
                        'retry_count' => 3
                    ],
                ]
            ]
        ];
    }
}
