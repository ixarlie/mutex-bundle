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
        $options = $processor->processConfiguration($configuration, array());
        $defaults = [
            'logger' => null
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
            'flock'     => ['cache_dir' => '%kernel.cache_dir%'],
            'memcache'  => ['client'    => '%memcache_client%'],
            'memcached' => ['client'    => '%memcached_client%'],
            'redis'     => ['client'    => '%redis_client%'],
//            'mysql'     => [
//                'username'   => 'dbuser',
//                'password'   => 'dbpassword',
//                'host'       => 'localhost',
//                'port'       => 3306,
//                'class_name' => 'PDO',
//            ],
            'logger' => null
        ];
        $this->assertEquals($expected, $options);
    }

    public function provideFullConfiguration()
    {
        $yaml = Yaml::parse(file_get_contents(__DIR__.'/Fixtures/config/full.yml'));
        $yaml = $yaml['i_xarlie_mutex'];
        return array(
            array($yaml),
        );
    }
}
