<?php

namespace Tests\DependencyInjection\Definition;

use IXarlie\MutexBundle\DependencyInjection\Definition\SemaphoreDefinition;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class SemaphoreDefinitionTest
 */
class SemaphoreDefinitionTest extends StoreDefinitionTestCase
{
    /**
     * @inheritdoc
     */
    protected function getClassName()
    {
        return '%ixarlie_mutex.semaphore_store.class%';
    }

    /**
     * @inheritdoc
     */
    protected function getDefinitionInstance()
    {
        return new SemaphoreDefinition();
    }

    /**
     * @inheritdoc
     */
    protected function getDefinitionName()
    {
        return 'semaphore';
    }

    /**
     * @inheritdoc
     */
    protected function assertStore(Definition $definition, array $configuration)
    {
        static::assertCount(0, $definition->getArguments());
        static::assertCount(0, $definition->getMethodCalls());
    }

    /**
     * @inheritdoc
     */
    public function getDefinitionProvider()
    {
        yield [
            [
                'default' => 'foo.bar',
            ]
        ];
        yield [
            [
                'default'     => 'foo.bar',
                'default_ttl' => 500,
            ]
        ];
        yield [
            [
                'default'     => 'foo.bar',
                'default_ttl' => 500,
                'logger'      => 'monolog.logger'
            ]
        ];
        yield [
            [
                'default'     => 'semaphore.default',
                'default_ttl' => 500,
            ]
        ];
        yield [
            [
                'default'     => 'semaphore.default',
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
                'foo' => null,
                'bar' => null
            ],
            [
                'foo' => [],
                'bar' => [],
            ]
        ];
        yield [
            [
                'foo' => [
                    'logger' => 'monolog.logger'
                ]
            ],
            [
                'foo' => [
                    'logger' => 'monolog.logger'
                ]
            ]
        ];
        yield [
            [
                'foo' => [
                    'blocking' => []
                ]
            ],
            [
                'foo' => [
                    'blocking' => [
                        'retry_sleep' => 100,
                        'retry_count' => PHP_INT_MAX,
                    ]
                ]
            ]
        ];
        yield [
            [
                'foo' => [
                    'blocking' => [
                        'retry_sleep' => 5,
                        'retry_count' => 10,
                    ]
                ]
            ],
            [
                'foo' => [
                    'blocking' => [
                        'retry_sleep' => 5,
                        'retry_count' => 10,
                    ]
                ]
            ]
        ];
    }
}
