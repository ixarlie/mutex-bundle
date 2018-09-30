<?php

namespace Tests\DependencyInjection\Definition;

use IXarlie\MutexBundle\DependencyInjection\Definition\CombinedDefinition;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Lock\Store\CombinedStore;
use Symfony\Component\Lock\Strategy\ConsensusStrategy;
use Symfony\Component\Lock\Strategy\UnanimousStrategy;

/**
 * Class CombinedDefinitionTest
 */
class CombinedDefinitionTest extends StoreDefinitionTestCase
{
    /**
     * @inheritdoc
     */
    protected function getClassName()
    {
        return CombinedStore::class;
    }

    /**
     * @inheritdoc
     */
    protected function getDefinitionInstance()
    {
        return new CombinedDefinition();
    }

    /**
     * @inheritdoc
     */
    protected function getDefinitionName()
    {
        return 'combined';
    }

    /**
     * @inheritdoc
     */
    protected function assertStore(Definition $definition, array $configuration)
    {
        static::assertCount(2, $definition->getArguments());

        // Assert stores arguments
        $stores = $definition->getArgument(0);

        static::assertCount(count($configuration['stores']), $stores);
        foreach ($stores as $i => $argument) {
            static::assertInstanceOf(Reference::class, $argument);
            static::assertEquals($configuration['stores'][$i], (string) $argument);
        }

        /** @var Definition $strategy */
        $strategy = $definition->getArgument(1);
        switch ($configuration['strategy']) {
            case 'consensus':
                static::assertInstanceOf(Definition::class, $strategy);
                static::assertEquals(ConsensusStrategy::class, $strategy->getClass());
                break;
            case 'unanimous':
                static::assertInstanceOf(Definition::class, $strategy);
                static::assertEquals(UnanimousStrategy::class, $strategy->getClass());
                break;
            default:
                static::assertInstanceOf(Reference::class, $strategy);
                static::assertEquals($configuration['strategy'], (string) $strategy);
                break;
        }
    }

    /**
     * @inheritdoc
     */
    public function getDefinitionProvider()
    {
        yield [
            [
                'default'  => 'foo.bar',
                'stores'   => ['my_store_service_one', 'my_store_service_two'],
                'strategy' => 'consensus',
                'logger'   => 'monolog.logger'
            ]
        ];
        yield [
            [
                'default'  => 'combined.default',
                'stores'   => ['my_store_service_one', 'my_store_service_two'],
                'strategy' => 'strategy_service',
                'logger'   => 'monolog.logger'
            ]
        ];
        yield [
            [
                'default'  => 'combined.default',
                'stores'   => ['my_store_service_one', 'my_store_service_two'],
                'strategy' => 'unanimous',
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
                    'stores' => [
                        'ixarlie_mutex.flock_store.default'
                    ]
                ]
            ],
            [
                'foo' => [
                    'stores' => [
                        'ixarlie_mutex.flock_store.default'
                    ],
                    'strategy' => 'unanimous'
                ]
            ]
        ];
        yield [
            [
                'foo' => [
                    'stores' => [
                        'ixarlie_mutex.flock_store.default',
                        'ixarlie_mutex.flock_store.extra',
                    ],
                    'strategy' => 'consensus'
                ]
            ],
            [
                'foo' => [
                    'stores' => [
                        'ixarlie_mutex.flock_store.default',
                        'ixarlie_mutex.flock_store.extra',
                    ],
                    'strategy' => 'consensus'
                ]
            ]
        ];
        yield [
            [
                'foo' => [
                    'stores' => [
                        'ixarlie_mutex.flock_store.default',
                        'ixarlie_mutex.flock_store.extra',
                    ],
                    'strategy' => 'app.strategy_service'
                ]
            ],
            [
                'foo' => [
                    'stores' => [
                        'ixarlie_mutex.flock_store.default',
                        'ixarlie_mutex.flock_store.extra',
                    ],
                    'strategy' => 'app.strategy_service'
                ]
            ]
        ];
        yield [
            [
                'foo' => [
                    'stores' => [
                        'ixarlie_mutex.flock_store.default',
                        'ixarlie_mutex.flock_store.extra',
                    ],
                    'logger' => 'monolog.logger'
                ]
            ],
            [
                'foo' => [
                    'stores' => [
                        'ixarlie_mutex.flock_store.default',
                        'ixarlie_mutex.flock_store.extra',
                    ],
                    'strategy' => 'unanimous',
                    'logger'   => 'monolog.logger'
                ]
            ]
        ];
        yield [
            [
                'foo' => [
                    'stores' => [
                        'ixarlie_mutex.flock_store.default',
                    ],
                    'blocking' => []
                ]
            ],
            [
                'foo' => [
                    'stores' => [
                        'ixarlie_mutex.flock_store.default',
                    ],
                    'strategy' => 'unanimous',
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
                    'stores' => [
                        'ixarlie_mutex.flock_store.default',
                    ],
                    'blocking' => [
                        'retry_sleep' => 5,
                        'retry_count' => 10,
                    ]
                ]
            ],
            [
                'foo' => [
                    'stores' => [
                        'ixarlie_mutex.flock_store.default',
                    ],
                    'strategy' => 'unanimous',
                    'blocking' => [
                        'retry_sleep' => 5,
                        'retry_count' => 10,
                    ]
                ]
            ]
        ];
    }
}
