<?php

namespace IXarlie\MutexBundle\Tests\DependencyInjection;

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
        $processor     = new Processor();
        $configuration = new Configuration();
        $options       = $processor->processConfiguration(
            $configuration,
            [
                'ixarlie_mutex' => [
                    'default' => 'none'
                ]
            ]
        );
        $defaults      = [
            'default'          => 'none',
            'flock'            => [],
            'memcache'         => [],
            'memcached'        => [],
            'redis'            => [],
            'predis'           => [],
            'request_listener' => [
                'enabled'             => true,
                'queue_max_try'       => 3,
                'queue_timeout'       => (int)ini_get('max_execution_time'),
                'http_exception'      => [
                    'message' => 'Resource is not available at this moment',
                    'code'    => 409
                ],
                'priority'            => 255,
                'request_placeholder' => false
            ]
        ];
        static::assertEquals($defaults, $options);
    }

    /**
     * @dataProvider provideFullConfiguration
     *
     * @param array $config
     */
    public function testFullConfiguration(array $config)
    {
        $processor     = new Processor();
        $configuration = new Configuration();
        $options       = $processor->processConfiguration($configuration, array($config));
        $expected      = [
            'default'          => 'flock.default',
            'flock'            => [
                'default' => [
                    'cache_dir' => '%kernel.cache_dir%',
                    'logger'    => null
                ]
            ],
            'redis'            => [
                'default' => [
                    'host'   => 'localhost',
                    'port'   => 6379,
                    'logger' => null
                ]
            ],
            'predis'           => [
                'default' => [
                    'connection' => [
                        'host' => 'localhost',
                        'port' => 6379,
                    ],
                    'options'    => [],
                    'logger'     => null
                ]
            ],
            'memcache'         => [],
            'memcached'        => [],
            'request_listener' => [
                'enabled'             => true,
                'queue_max_try'       => 3,
                'queue_timeout'       => (int)ini_get('max_execution_time'),
                'http_exception'      => [
                    'message' => 'Resource is not available at this moment',
                    'code'    => 409
                ],
                'priority'            => 255,
                'request_placeholder' => false
            ]
        ];
        static::assertEquals($expected, $options);
    }

    public function provideFullConfiguration()
    {
        $yaml = Yaml::parse(file_get_contents(__DIR__ . '/../Fixtures/config/basic.yml'));
        $yaml = $yaml['i_xarlie_mutex'];

        yield [$yaml];
    }
}
