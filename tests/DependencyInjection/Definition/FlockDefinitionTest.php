<?php

namespace Tests\DependencyInjection\Definition;

use IXarlie\MutexBundle\DependencyInjection\Definition\FlockDefinition;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class FlockDefinitionTest
 */
class FlockDefinitionTest extends StoreDefinitionTestCase
{
    /**
     * @inheritdoc
     */
    protected function getClassName()
    {
        return '%ixarlie_mutex.flock_store.class%';
    }

    /**
     * @inheritdoc
     */
    protected function getDefinitionInstance()
    {
        return new FlockDefinition();
    }

    /**
     * @inheritdoc
     */
    protected function getDefinitionName()
    {
        return 'flock';
    }

    /**
     * @inheritdoc
     */
    protected function assertStore(Definition $definition, array $configuration)
    {
        static::assertCount(1, $definition->getArguments());
        $path = $definition->getArgument(0);

        static::assertInternalType('string', $path);
    }

    /**
     * @inheritdoc
     */
    public function getDefinitionProvider()
    {
        yield [
            [
                'default'   => 'foo.bar',
                'lock_dir' => '/tmp/locks',
            ]
        ];
        yield [
            [
                'default'   => 'foo.bar',
                'lock_dir' => '/tmp/locks',
                'logger'    => 'monolog.logger'
            ]
        ];
        yield [
            [
                'default'   => 'flock.default',
                'lock_dir' => '/tmp/locks',
                'logger'    => 'monolog.logger'
            ]
        ];
        yield [
            [
                'default'   => 'flock.default',
                'lock_dir' => '/tmp/locks',
                'logger'   => 'monolog.logger',
                'blocking' => [
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
                    'lock_dir' => '/tmp/flock'
                ]
            ],
            [
                'foo' => [
                    'lock_dir' => '/tmp/flock'
                ]
            ]
        ];
        yield [
            [
                'foo' => [
                    'lock_dir' => '/tmp/flock',
                    'logger'    => 'monolog.logger'
                ]
            ],
            [
                'foo' => [
                    'lock_dir' => '/tmp/flock',
                    'logger'    => 'monolog.logger'
                ]
            ]
        ];
        yield [
            [
                'foo' => [
                    'lock_dir' => '/tmp/flock',
                    'blocking'  => []
                ]
            ],
            [
                'foo' => [
                    'lock_dir' => '/tmp/flock',
                    'blocking'  => [
                        'retry_sleep' => 100,
                        'retry_count' => PHP_INT_MAX,
                    ]
                ]
            ]
        ];
        yield [
            [
                'foo' => [
                    'lock_dir' => '/tmp/flock',
                    'blocking'  => [
                        'retry_sleep' => 5,
                        'retry_count' => 10,
                    ]
                ]
            ],
            [
                'foo' => [
                    'lock_dir' => '/tmp/flock',
                    'blocking'  => [
                        'retry_sleep' => 5,
                        'retry_count' => 10,
                    ]
                ]
            ]
        ];
    }
}
