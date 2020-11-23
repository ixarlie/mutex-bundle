<?php

namespace Tests\DependencyInjection;

use IXarlie\MutexBundle\DependencyInjection\Configuration;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ConfigurationTest
 */
final class ConfigurationTest extends TestCase
{
    public function testDefaults(): void
    {
        $options  = [
            'i_xarlie_mutex' => [
                'default'          => 'none',
                'request_listener' => null,
            ],
        ];
        $result   = $this->processConfiguration($options);
        $expected = [
            'default'          => 'none',
            'request_listener' => [
                'enabled'     => false,
                'priority'    => 255,
                'autorelease' => true,
            ],
            'flock'            => [],
            'semaphore'        => [],
            'redis'            => [],
            'memcached'        => [],
            'combined'         => [],
            'custom'           => [],
            'pdo'              => [],
            'zookeeper'        => [],
        ];

        static::assertEquals($expected, $result);
    }

    public function testStoresConfiguration(): void
    {
        $yaml     = Yaml::parse(file_get_contents(__DIR__ . '/../Fixtures/config/stores.yaml'));
        $result   = $this->processConfiguration($yaml);
        $expected = [
            'default'          => 'flock.default',
            'flock'            => [
                'default' => [
                    'lock_dir' => '%kernel.cache_dir%',
                ],
                'flock1'  => [
                    'lock_dir' => '/tmp/flock',
                    'logger'   => 'monolog.logger',
                ],
            ],
            'redis'            => [
                'default' => [
                    'client'      => 'redis_client_1',
                    'default_ttl' => 300,
                ],
                'redis1'  => [
                    'client'      => 'redis_client_1',
                    'default_ttl' => 1000,
                    'blocking'    => [
                        'retry_count' => 3,
                        'retry_sleep' => 100,
                    ],
                    'logger'      => 'monolog.logger',
                ],
            ],
            'memcached'        => [
                'default'    => [
                    'client'      => 'memcached_client_1',
                    'default_ttl' => 300,
                ],
                'memcached1' => [
                    'client'      => 'memcached_client_1',
                    'default_ttl' => 1000,
                    'blocking'    => [
                        'retry_count' => 3,
                        'retry_sleep' => 100,
                    ],
                    'logger'      => 'monolog.logger',
                ],
            ],
            'semaphore'        => [
                'default'    => [],
                'semaphore1' => [
                    'logger' => 'monolog.logger',
                ],
            ],
            'combined'         => [
                'default'   => [
                    'stores'   => [
                        'ixarlie_mutex.semaphore_store.default',
                        'ixarlie_mutex.flock_store.default',
                    ],
                    'strategy' => 'unanimous',
                ],
                'combined1' => [
                    'stores'   => [
                        'ixarlie_mutex.semaphore_store.default',
                        'ixarlie_mutex.flock_store.default',
                    ],
                    'strategy' => 'unanimous',
                    'blocking' => [
                        'retry_count' => 3,
                        'retry_sleep' => 100,
                    ],
                    'logger'   => 'monolog.logger',
                ],
            ],
            'custom'           => [
                'default' => [
                    'service' => 'my_custom_implementation',
                ],
                'custom1' => [
                    'service' => 'my_custom_implementation',
                    'logger'  => 'monolog.logger',
                ],
            ],
            'pdo'              => [
                'default' => [
                    'dsn'                   => 'mysql:host=127.0.0.1;dbname=lock',
                    'db_table'              => 'lock_keys',
                    'db_id_col'             => 'key_id',
                    'db_token_col'          => 'key_token',
                    'db_expiration_col'     => 'key_expiration',
                    'db_connection_options' => [],
                ],
                'pdo1'    => [
                    'dsn'                   => 'mysql:host=127.0.0.1;dbname=lock',
                    'db_table'              => 'my_table',
                    'db_id_col'             => 'my_id_col',
                    'db_token_col'          => 'my_token_col',
                    'db_expiration_col'     => 'my_expiration_col',
                    'logger'                => 'monolog.logger',
                    'db_connection_options' => [
                        'foo' => 'bar',
                    ],
                ],
            ],
            'zookeeper'        => [
                'default'    => [
                    'client' => 'my_custom_implementation',
                ],
                'zookeeper1' => [
                    'client' => 'my_custom_implementation',
                    'logger' => 'monolog.logger',
                ],
            ],
            'request_listener' => [
                'enabled'     => false,
                'priority'    => 255,
                'autorelease' => true,
            ],
        ];

        static::assertEquals($expected, $result);
    }

    public function testRequestListenerConfiguration(): void
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
            'pdo'              => [],
            'zookeeper'        => [],
        ];

        static::assertEquals($expected, $result);
    }

    /**
     * @param array $options
     *
     * @return array
     */
    private function processConfiguration(array $options): array
    {
        $processor     = new Processor();
        $configuration = new Configuration();

        $options = $processor->processConfiguration($configuration, $options);

        return $options;
    }
}
