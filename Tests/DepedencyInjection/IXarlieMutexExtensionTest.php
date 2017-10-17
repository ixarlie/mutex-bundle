<?php

namespace IXarlie\MutexBundle\Tests\DependencyInjection;

use IXarlie\MutexBundle\DependencyInjection\IXarlieMutexExtension;
use IXarlie\MutexBundle\Lock\RedisLock;
use IXarlie\MutexBundle\Manager\LockerManagerInterface;
use IXarlie\MutexBundle\Tests\Util\UtilTestTrait;
use NinjaMutex\Lock\FlockLock;
use NinjaMutex\Lock\MemcachedLock;
use NinjaMutex\Lock\MemcacheLock;
use NinjaMutex\Lock\PredisRedisLock;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * Class IXarlieMutexExtensionTest
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class IXarlieMutexExtensionTest extends \PHPUnit_Framework_TestCase
{
    use UtilTestTrait;
    
    public function testInstance()
    {
        $this->assertInstanceOf(ExtensionInterface::class, new IXarlieMutexExtension());
    }
    
    public function testPlainConfiguration()
    {
        $container = $this->getContainer();
        $loader    = new IXarlieMutexExtension();
        $loader->load([
            [
                'default'        => 'flock.mylocker',
                'flock' => [
                    'mylocker' => [
                        'cache_dir' => '/tmp'
                    ]
                ],
                'request_listener' => [
                    'queue_timeout'  => 30,
                    'queue_max_try'  => 5,
                    'translator'     => true,
                    'user_isolation' => true,
                    'http_exception' => [
                        'message'    => 'You shall not pass!',
                        'code'       => 409
                    ]
                ]
            ]
        ], $container);
        $this->assertTrue(true);
    }

    /**
     * @dataProvider bundleConfigurations
     */
    public function testLockerConfiguration($className, $type, $config, $dependencyClass = null)
    {
        if ($dependencyClass && !class_exists($dependencyClass)) {
            $this->markTestSkipped($dependencyClass . ' is not installed/configured');
        }

        $serviceId = 'i_xarlie_mutex.locker_' . $type . '.mylocker';

        $container = $this->getContainer();
        $loader    = new IXarlieMutexExtension();
        $loader->load([
            [
                'default' => $type . '.mylocker',
                $type => $config
            ]
        ], $container);

        $manager = $container->get($serviceId);
        $this->assertInstanceOf(LockerManagerInterface::class, $manager);

        $refl = new \ReflectionClass($manager);
        $prop = $refl->getProperty('locker');
        $prop->setAccessible(true);

        $locker = $prop->getValue($manager);
        $this->assertInstanceOf($className, $locker);

        // test alias
        $alias = $container->get('i_xarlie_mutex.locker');
        $this->assertEquals($manager, $alias);
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @expectedExceptionMessage The service "i_xarlie_mutex.locker" has a dependency on a non-existent service "i_xarlie_mutex.locker_flock.foo"
     */
    public function testInvalidDefault()
    {
        $container = $this->getContainer();
        $loader = new IXarlieMutexExtension();
        $loader->load([
            [
                'default' => 'flock.foo',
                'flock' => [
                    'mylocker' => [
                        'cache_dir' => '%kernel.cache_dir'
                    ]
                ]
            ]
        ], $container);
    }

    public function bundleConfigurations()
    {
        return [
            [
                'class'  => FlockLock::class,
                'type'   => 'flock',
                'config' => [
                    'mylocker' => ['cache_dir' => '%kernel.cache_dir%']
                ]
            ],
            [
                'class'  => RedisLock::class,
                'type'   => 'redis',
                'config' => [
                    'mylocker' => ['host' => '127.0.0.1', 'port' => 6379]
                ],
                '\Redis'
            ],
            [
                'class'  => PredisRedisLock::class,
                'type'   => 'predis',
                'config' => [
                    'mylocker' => ['connection' => 'tcp://127.0.0.1:6379']
                ],
                '\Predis\Client'
            ],
            [
                'class'  => MemcacheLock::class,
                'type'   => 'memcache',
                'config' => [
                    'mylocker' => ['host' => '127.0.0.1', 'port' => 6379]
                ],
                '\Memcache'
            ],
            [
                'class'  => MemcachedLock::class,
                'type'   => 'memcached',
                'config' => [
                    'mylocker' => ['host' => '127.0.0.1', 'port' => 6379]
                ],
                '\Memcached'
            ],

        ];
    }
}
