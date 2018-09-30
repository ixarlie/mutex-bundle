<?php

namespace Tests\DependencyInjection;

use IXarlie\MutexBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ConfigurationTest
 */
class ConfigurationTest extends TestCase
{
    public function testDefaults()
    {
        $options  = [
            'i_xarlie_mutex' => [
                'default'          => 'none',
                'request_listener' => null
            ]
        ];
        $result   = $this->processConfiguration($options);
        $expected = [
            'default'          => 'none',
            'request_listener' => [
                'enabled'     => false,
                'priority'    => 255,
                'autorelease' => true
            ],
            'flock'            => [],
            'semaphore'        => [],
            'redis'            => [],
            'memcached'        => [],
            'combined'         => [],
            'custom'           => [],
        ];

        static::assertEquals($expected, $result);
    }

    public function testStoresConfiguration()
    {
        $yaml      = Yaml::parse(file_get_contents(__DIR__ . '/../Fixtures/config/stores.yaml'));
        $result    = $this->processConfiguration($yaml);
        $expected = [
            'default' => 'flock.default',
            'flock' => [
                'default' => [
                    'lock_dir' => '%kernel.cache_dir%'
                ],
                'flock1' => [
                    'lock_dir' => '/tmp/flock',
                    'blocking'  => [
                        'retry_count' => 3,
                        'retry_sleep' => 100
                    ],
                    'logger' => 'monolog.logger'
                ],
            ],
            'redis' => [
                'default' => [
                    'client'      => 'redis_client_1',
                    'default_ttl' => 300,
                ],
                'redis1' => [
                    'client'      => 'redis_client_1',
                    'default_ttl' => 1000,
                    'blocking'    => [
                        'retry_count' => 3,
                        'retry_sleep' => 100
                    ],
                    'logger'      => 'monolog.logger',
                ]
            ],
            'memcached' => [
                'default' => [
                    'client'      => 'memcached_client_1',
                    'default_ttl' => 300,
                ],
                'memcached1' => [
                    'client'      => 'memcached_client_1',
                    'default_ttl' => 1000,
                    'blocking'    => [
                        'retry_count' => 3,
                        'retry_sleep' => 100
                    ],
                    'logger'      => 'monolog.logger',
                ]
            ],
            'semaphore' => [
                'default'    => [],
                'semaphore1' => [
                    'blocking'    => [
                        'retry_count' => 3,
                        'retry_sleep' => 100
                    ],
                    'logger'      => 'monolog.logger',
                ]
            ],
            'combined' => [
                'default'    => [
                    'stores' => [
                        'ixarlie_mutex.semaphore_store.default',
                        'ixarlie_mutex.flock_store.default'
                    ],
                    'strategy' => 'unanimous'
                ],
                'combined1' => [
                    'stores'   => [
                        'ixarlie_mutex.semaphore_store.default',
                        'ixarlie_mutex.flock_store.default'
                    ],
                    'strategy' => 'unanimous',
                    'blocking' => [
                        'retry_count' => 3,
                        'retry_sleep' => 100
                    ],
                    'logger'   => 'monolog.logger',
                ]
            ],
            'custom' => [
                'default' => [
                    'service'  => 'my_custom_implementation',
                ],
                'custom1' => [
                    'service'  => 'my_custom_implementation',
                    'blocking' => [
                        'retry_count' => 3,
                        'retry_sleep' => 100
                    ],
                    'logger'   => 'monolog.logger',
                ]
            ],
            'request_listener' => [
                'enabled'     => false,
                'priority'    => 255,
                'autorelease' => true
            ],
        ];

        static::assertEquals($expected, $result);
    }

    public function testRequestListenerConfiguration()
    {
        $yaml     = Yaml::parse(file_get_contents(__DIR__ . '/../Fixtures/config/listener.yaml'));
        $result   = $this->processConfiguration($yaml);
        $expected = [
            'default'          => 'none',
            'request_listener' => [
                'enabled'     => true,
                'priority'    => 1000,
                'autorelease' => true,
            ],
            'flock'            => [],
            'redis'            => [],
            'memcached'        => [],
            'semaphore'        => [],
            'combined'         => [],
            'custom'           => [],
        ];

        static::assertEquals($expected, $result);
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private function processConfiguration(array $options)
    {
        $processor     = new Processor();
        $configuration = new Configuration();

        $options = $processor->processConfiguration($configuration, $options);

        return $options;
    }
}
