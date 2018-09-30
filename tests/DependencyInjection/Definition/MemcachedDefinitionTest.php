<?php

namespace Tests\DependencyInjection\Definition;

use IXarlie\MutexBundle\DependencyInjection\Definition\MemcachedDefinition;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class MemcachedDefinitionTest
 */
class MemcachedDefinitionTest extends StoreDefinitionTestCase
{
    /**
     * @inheritdoc
     */
    protected function getClassName()
    {
        return '%ixarlie_mutex.memcached_store.class%';
    }

    /**
     * @inheritdoc
     */
    protected function getDefinitionInstance()
    {
        return new MemcachedDefinition();
    }

    /**
     * @inheritdoc
     */
    protected function getDefinitionName()
    {
        return 'memcached';
    }

    /**
     * @inheritdoc
     */
    protected function assertStore(Definition $definition, array $configuration)
    {
        static::assertCount(2, $definition->getArguments());

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
                'client'      => 'memcached_client',
                'default_ttl' => 500
            ]
        ];
        yield [
            [
                'default'     => 'foo.bar',
                'client'      => 'memcached_client',
                'default_ttl' => 500,
                'logger'      => 'monolog.logger'
            ]
        ];
        yield [
            [
                'default'     => 'memcached.default',
                'client'      => 'memcached_client',
                'default_ttl' => 500,
                'logger'      => 'monolog.logger'
            ]
        ];
        yield [
            [
                'default'     => 'memcached.default',
                'client'      => 'memcached_client',
                'default_ttl' => 500,
                'logger'      => 'monolog.logger',
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
                    'client' => 'memcached_client',
                ]
            ],
            [
                'foo' => [
                    'client'      => 'memcached_client',
                    'default_ttl' => 300
                ]
            ]
        ];
        yield [
            [
                'foo' => [
                    'client'      => 'memcached_client',
                    'default_ttl' => 900
                ]
            ],
            [
                'foo' => [
                    'client'      => 'memcached_client',
                    'default_ttl' => 900
                ]
            ]
        ];
        yield [
            [
                'foo' => [
                    'client' => 'memcached_client',
                    'logger' => 'monolog.logger'
                ]
            ],
            [
                'foo' => [
                    'client'      => 'memcached_client',
                    'default_ttl' => 300,
                    'logger'      => 'monolog.logger'
                ]
            ]
        ];
        yield [
            [
                'foo' => [
                    'client'   => 'memcached_client',
                    'blocking' => []
                ]
            ],
            [
                'foo' => [
                    'client'      => 'memcached_client',
                    'default_ttl' => 300,
                    'blocking'    => [
                        'retry_sleep' => 100,
                        'retry_count' => PHP_INT_MAX,
                    ]
                ]
            ]
        ];
        yield [
            [
                'foo' => [
                    'client'   => 'memcached_client',
                    'blocking' => [
                        'retry_sleep' => 5,
                        'retry_count' => 10,
                    ]
                ]
            ],
            [
                'foo' => [
                    'client'      => 'memcached_client',
                    'default_ttl' => 300,
                    'blocking'    => [
                        'retry_sleep' => 5,
                        'retry_count' => 10,
                    ]
                ]
            ]
        ];
    }
}
