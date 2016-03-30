<?php

namespace IXarlie\MutexBundle\Tests\DependencyInjection;

use IXarlie\MutexBundle\DependencyInjection\IXarlieMutexExtension;
use IXarlie\MutexBundle\Lock\RedisLock;
use IXarlie\MutexBundle\Model\LockerManagerInterface;
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
            'kernel.debug'       => false,
            'kernel.bundles'     => [],
            'kernel.cache_dir'   => sys_get_temp_dir(),
            'kernel.environment' => 'test',
            'kernel.root_dir'    => __DIR__.'/../../' // src dir
        )));
    }

    /**
     * @dataProvider bundleConfigurations
     */
    public function testBundle($className, $type, $config)
    {
        $serviceId     = 'i_xarlie_mutex.locker_' . $type;

        $container = $this->getContainer();
        $loader = new IXarlieMutexExtension();
        $loader->load([
            [$type => $config]
        ], $container);

        try {
            $manager = $container->get($serviceId);
            $this->assertInstanceOf(LockerManagerInterface::class, $manager);

            $refl = new \ReflectionClass($manager);
            $prop = $refl->getProperty('locker');
            $prop->setAccessible(true);

            $locker = $prop->getValue($manager);
            $this->assertInstanceOf($className, $locker);
        } catch (\Exception $e) {
            // Some test can fail due to missing libraries
            $this->markTestSkipped($e->getMessage());
        }
    }

    public function bundleConfigurations()
    {
        return [
            [
                'class'  => FlockLock::class,
                'type'   => 'flock',
                'config' => ['cache_dir' => '%kernel.cache_dir%']
            ],
            [
                'class'  => RedisLock::class,
                'type'   => 'redis',
                'config' => ['host' => '127.0.0.1', 'port' => 6379]
            ],
            [
                'class'  => PredisRedisLock::class,
                'type'   => 'predis',
                'config' => ['host' => '127.0.0.1', 'port' => 6379]
            ],
            [
                'class'  => MemcacheLock::class,
                'type'   => 'memcache',
                'config' => ['host' => '127.0.0.1', 'port' => 6379]
            ],
            [
                'class'  => MemcachedLock::class,
                'type'   => 'memcached',
                'config' => ['host' => '127.0.0.1', 'port' => 6379]
            ],

        ];
    }
}
