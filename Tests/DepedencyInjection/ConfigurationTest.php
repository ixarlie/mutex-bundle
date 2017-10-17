<?php

namespace IXarlie\MutexBundle\Tests\DependencyInjection;

use IXarlie\MutexBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Yaml\Yaml;

/**
 * Class ConfigurationTest
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testDefaults()
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $options = $processor->processConfiguration(
            $configuration,
            [
                'ixarlie_mutex' => [
                    'default' => 'none'
                ]
            ]
        );
        $defaults = [
            'default' => 'none',
            'flock'     => [],
            'memcache'  => [],
            'memcached' => [],
            'redis'     => [],
            'predis'    => [],
            'request_listener' => [
                'queue_max_try' => 3,
                'queue_timeout' => (int) ini_get('max_execution_time'),
                'http_exception' => [
                    'message' => 'Resource is not available at this moment',
                    'code' => 409
                ]
            ]
        ];
        $this->assertEquals($defaults, $options);
    }

    /**
     * @dataProvider provideFullConfiguration
     */
    public function testFullConfiguration($config)
    {
        $processor = new Processor();
        $configuration = new Configuration();
        $options = $processor->processConfiguration($configuration, array($config));
        $expected = [
            'default' => 'flock.default',
            'flock'     => [
                'default' => [
                    'cache_dir' => '%kernel.cache_dir%',
                    'logger'    => null
                ]
            ],
            'redis'     => [
                'default' => [
                    'host'      => 'localhost',
                    'port'      => 6379,
                    'logger'    => null
                ]
            ],
            'predis'     => [
                'default' => [
                    'connection' => [
                        'host'      => 'localhost',
                        'port'      => 6379,    
                    ],
                    'options'   => [],
                    'logger'    => null
                ]
            ],
            'memcache'  => [],
            'memcached' => [],
            'request_listener' => [
                'queue_max_try' => 3,
                'queue_timeout' => (int) ini_get('max_execution_time'),
                'http_exception' => [
                    'message' => 'Resource is not available at this moment',
                    'code' => 409
                ]
            ]
        ];
        $this->assertEquals($expected, $options);
    }

    public function provideFullConfiguration()
    {
        $yaml = Yaml::parse(file_get_contents(__DIR__ . '/../Fixtures/config/basic.yml'));
        $yaml = $yaml['i_xarlie_mutex'];
        return array(
            array($yaml),
        );
    }
}
