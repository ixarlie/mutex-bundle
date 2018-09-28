<?php

namespace IXarlie\MutexBundle\Tests\DependencyInjection\Definition;

use IXarlie\MutexBundle\DependencyInjection\Definition\LockDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\RedisDefinition;
use IXarlie\MutexBundle\Tests\Util\UtilTestTrait;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class RedisDefinitionTest
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class RedisDefinitionTest extends \PHPUnit_Framework_TestCase
{
    use UtilTestTrait;

    public function testInstanceOf()
    {
        static::assertInstanceOf(LockDefinition::class, new RedisDefinition('default'));
    }

    public function testConfigure()
    {
        $container  = $this->getContainer();
        $service    = $this->getServiceDefinition();
        $definition = new RedisDefinition('default');

        static::assertEmpty($service->getArguments());

        $config = ['host' => '127.0.0.1', 'port' => 6379, 'password' => '1234', 'database' => 2];
        $config = $this->processConfiguration('redis', $config);

        $definition->createFactory($config, $service, $container);
        static::assertCount(1, $service->getArguments());

        /** @var Definition $locker */
        $locker = $service->getArgument(0);
        static::assertInstanceOf(Definition::class, $locker);
        static::assertEquals('%ninja_mutex.locker_redis_class%', $locker->getClass());
        static::assertCount(1, $locker->getArguments());
        /** @var Definition $conn */
        $conn = $locker->getArgument(0);
        static::assertInstanceOf(Definition::class, $conn);
        static::assertEquals('%i_xarlie_mutex.redis.connection.class%', $conn->getClass());
        static::assertCount(3, $conn->getMethodCalls());
        static::assertCount(0, $conn->getArguments());
        static::assertFalse($conn->isPublic());

        $calls = $conn->getMethodCalls();
        static::assertEquals('connect', $calls[0][0]);
        static::assertEquals([$config['host'], $config['port']], $calls[0][1]);
        static::assertEquals('auth', $calls[1][0]);
        static::assertEquals([$config['password']], $calls[1][1]);
        static::assertEquals('select', $calls[2][0]);
        static::assertEquals([$config['database']], $calls[2][1]);
    }

    public function testConfigureLogger()
    {
        $container  = $this->getContainer();
        $service    = $this->getServiceDefinition();
        $definition = new RedisDefinition('default');

        static::assertEmpty($service->getArguments());

        $container->setDefinition('logger', new Definition('%logger.class%'));

        $config = ['host' => '127.0.0.1', 'port' => 6379, 'logger' => 'logger'];
        $config = $this->processConfiguration('redis', $config);
        $definition->createFactory($config, $service, $container);

        static::assertCount(2, $service->getArguments());
        static::assertEquals('%logger.class%', $service->getArgument(1)->getClass());
    }
}
