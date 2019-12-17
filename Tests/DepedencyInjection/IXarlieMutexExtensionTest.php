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
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * Class IXarlieMutexExtensionTest
 */
class IXarlieMutexExtensionTest extends TestCase
{
    use UtilTestTrait;

    public function testInstance()
    {
        static::assertInstanceOf(ExtensionInterface::class, new IXarlieMutexExtension());
    }

    public function testPlainConfiguration()
    {
        $container = $this->getContainer();
        $loader    = new IXarlieMutexExtension();
        $loader->load([
            [
                'default'          => 'flock.mylocker',
                'flock'            => [
                    'mylocker' => [
                        'cache_dir' => '/tmp',
                    ],
                ],
                'request_listener' => [
                    'queue_timeout'  => 30,
                    'queue_max_try'  => 5,
                    'translator'     => true,
                    'user_isolation' => true,
                    'http_exception' => [
                        'message' => 'You shall not pass!',
                        'code'    => 409,
                    ],
                ],
            ],
        ], $container);

        static::expectNotToPerformAssertions();
    }

    /**
     * @dataProvider bundleConfigurations
     *
     * @param string      $className
     * @param string      $type
     * @param array       $config
     * @param string|null $dependencyClass
     */
    public function testLockerConfiguration(string $className, string $type, array $config, ?string $dependencyClass)
    {
        if ($dependencyClass && !class_exists($dependencyClass)) {
            static::markTestSkipped($dependencyClass . ' is not installed/configured');
        }

        $serviceId = 'i_xarlie_mutex.locker_' . $type . '.mylocker';

        $container = $this->getContainer();
        $loader    = new IXarlieMutexExtension();
        $loader->load([
            [
                'default' => $type . '.mylocker',
                $type     => $config,
            ],
        ], $container);

        $manager = $container->get($serviceId);
        static::assertInstanceOf(LockerManagerInterface::class, $manager);

        $refl = new \ReflectionClass($manager);
        $prop = $refl->getProperty('locker');
        $prop->setAccessible(true);

        $locker = $prop->getValue($manager);
        static::assertInstanceOf($className, $locker);

        // test alias
        $alias = $container->get('i_xarlie_mutex.locker');
        static::assertEquals($manager, $alias);
    }

    public function testInvalidDefault()
    {
        static::expectException(ServiceNotFoundException::class);
        static::expectExceptionMessage('The service "i_xarlie_mutex.locker" has a dependency on a non-existent service "i_xarlie_mutex.locker_flock.foo"');

        $container = $this->getContainer();
        $loader    = new IXarlieMutexExtension();
        $loader->load([
            [
                'default' => 'flock.foo',
                'flock'   => [
                    'mylocker' => [
                        'cache_dir' => '%kernel.cache_dir',
                    ],
                ],
            ],
        ], $container);
    }

    public function bundleConfigurations()
    {
        yield [
            'class'  => FlockLock::class,
            'type'   => 'flock',
            'config' => [
                'mylocker' => ['cache_dir' => '%kernel.cache_dir%'],
            ],
            null,
        ];
        yield [
            'class'  => RedisLock::class,
            'type'   => 'redis',
            'config' => [
                'mylocker' => ['host' => '127.0.0.1', 'port' => 6379],
            ],
            '\Redis',
        ];
        yield [
            'class'  => PredisRedisLock::class,
            'type'   => 'predis',
            'config' => [
                'mylocker' => ['connection' => 'tcp://127.0.0.1:6379'],
            ],
            '\Predis\Client',
        ];
        yield [
            'class'  => MemcacheLock::class,
            'type'   => 'memcache',
            'config' => [
                'mylocker' => ['host' => '127.0.0.1', 'port' => 6379],
            ],
            '\Memcache',
        ];
        yield [
            'class'  => MemcachedLock::class,
            'type'   => 'memcached',
            'config' => [
                'mylocker' => ['host' => '127.0.0.1', 'port' => 6379],
            ],
            '\Memcached',
        ];
    }
}
