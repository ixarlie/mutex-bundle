<?php

namespace IXarlie\MutexBundle\Tests\DependencyInjection\Definition;

use IXarlie\MutexBundle\DependencyInjection\Definition\LockDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\MemcacheDefinition;
use IXarlie\MutexBundle\Tests\Util\UtilTestTrait;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class MemcacheDefinitionTest
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class MemcacheDefinitionTest extends \PHPUnit_Framework_TestCase
{
    use UtilTestTrait;

    public function testInstanceOf()
    {
        static::assertInstanceOf(LockDefinition::class, new MemcacheDefinition('default'));
    }

    public function testConfigure()
    {
        $container  = $this->getContainer();
        $service    = $this->getServiceDefinition();
        $definition = new MemcacheDefinition('default');

        static::assertEmpty($service->getArguments());

        $config = $this->processConfiguration('memcache', ['host' => '127.0.0.1', 'port' => 6379]);

        $definition->configure($config, $service, $container);
        static::assertCount(1, $service->getArguments());

        /** @var Definition $locker */
        $locker = $service->getArgument(0);
        static::assertInstanceOf(Definition::class, $locker);
        static::assertEquals('%ninja_mutex.locker_memcache_class%', $locker->getClass());
        static::assertCount(1, $locker->getArguments());
        /** @var Definition $conn */
        $conn = $locker->getArgument(0);
        static::assertInstanceOf(Definition::class, $conn);
        static::assertEquals('%i_xarlie_mutex.memcache.connection.class%', $conn->getClass());
        static::assertCount(0, $conn->getArguments());
        static::assertFalse($conn->isPublic());
        static::assertCount(1, $conn->getMethodCalls());

        $calls = $conn->getMethodCalls();

        static::assertEquals('addserver', $calls[0][0]);
        static::assertEquals([$config['host'], $config['port']], $calls[0][1]);
    }

    public function testConfigureLogger()
    {
        $container  = $this->getContainer();
        $service    = $this->getServiceDefinition();
        $definition = new MemcacheDefinition('default');

        static::assertEmpty($service->getArguments());

        $container->setDefinition('logger', new Definition('%logger.class%'));

        $config = ['host' => '127.0.0.1', 'port' => 6379, 'logger' => 'logger'];
        $config = $this->processConfiguration('memcache', $config);
        $definition->configure($config, $service, $container);

        static::assertCount(2, $service->getArguments());
        static::assertEquals('%logger.class%', $service->getArgument(1)->getClass());
    }
}
