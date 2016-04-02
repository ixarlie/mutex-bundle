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
        $configuration = new Configuration(false);
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
        ];
        $this->assertEquals($defaults, $options);
    }

    /**
     * @dataProvider provideFullConfiguration
     */
    public function testFullConfiguration($config)
    {
        $processor = new Processor();
        $configuration = new Configuration(false);
        $options = $processor->processConfiguration($configuration, array($config));
        $expected = [
            'default' => 'flock_default',
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
                    'host'      => 'localhost',
                    'port'      => 6379,
                    'logger'    => null
                ]
            ],
            'memcache'  => [],
            'memcached' => []
        ];
        $this->assertEquals($expected, $options);
    }

    public function provideFullConfiguration()
    {
        $yaml = Yaml::parse(file_get_contents(__DIR__ . '/Fixtures/config/basic.yml'));
        $yaml = $yaml['i_xarlie_mutex'];
        return array(
            array($yaml),
        );
    }
}
