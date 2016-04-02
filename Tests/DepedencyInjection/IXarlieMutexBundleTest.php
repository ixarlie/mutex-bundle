<?php

namespace IXarlie\MutexBundle\Tests\DependencyInjection;

use IXarlie\MutexBundle\DependencyInjection\IXarlieMutexExtension;
use IXarlie\MutexBundle\Lock\RedisLock;
use IXarlie\MutexBundle\Manager\LockerManagerInterface;
use NinjaMutex\Lock\FlockLock;
use NinjaMutex\Lock\MemcachedLock;
use NinjaMutex\Lock\MemcacheLock;
use NinjaMutex\Lock\PredisRedisLock;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

/**
 * Class IXarlieMutexBundleTest
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class IXarlieMutexBundleTest extends \PHPUnit_Framework_TestCase
{
    private function getContainer()
    {
        return new ContainerBuilder(new ParameterBag(array(
            'kernel.debug' => false,
            'kernel.bundles' => [],
            'kernel.cache_dir' => sys_get_temp_dir(),
            'kernel.environment' => 'test',
            'kernel.root_dir' => __DIR__ . '/../../' // src dir
        )));
    }

    /**
     * @dataProvider bundleConfigurations
     */
    public function testBundle($className, $type, $config)
    {
        $serviceId = 'i_xarlie_mutex.locker_' . $type . '.mylocker';

        $container = $this->getContainer();
        $loader = new IXarlieMutexExtension();
        $loader->load([
            [
                'default' => $type . '.mylocker',
                $type => $config
            ]
        ], $container);

        try {
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
        } catch (\ReflectionException $e) {
            // Some test can fail due to missing libraries
            $this->markTestSkipped($e->getMessage());
        }
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
                ]
            ],
            [
                'class'  => PredisRedisLock::class,
                'type'   => 'predis',
                'config' => [
                    'mylocker' => ['host' => '127.0.0.1', 'port' => 6379]
                ]
            ],
            [
                'class'  => MemcacheLock::class,
                'type'   => 'memcache',
                'config' => [
                    'mylocker' => ['host' => '127.0.0.1', 'port' => 6379]
                ]
            ],
            [
                'class'  => MemcachedLock::class,
                'type'   => 'memcached',
                'config' => [
                    'mylocker' => ['host' => '127.0.0.1', 'port' => 6379]
                ]
            ],

        ];
    }
}
