<?php

namespace IXarlie\MutexBundle\Tests\DependencyInjection\Definition;

use IXarlie\MutexBundle\DependencyInjection\Definition\LockDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\MemcachedDefinition;
use IXarlie\MutexBundle\Tests\Util\UtilTestTrait;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class MemcachedDefinitionTest
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class MemcachedDefinitionTest extends \PHPUnit_Framework_TestCase
{
    use UtilTestTrait;

    public function testInstanceOf()
    {
        $this->assertInstanceOf(LockDefinition::class, new MemcachedDefinition('default'));
    }

    public function testConfigure()
    {
        $container  = $this->getContainer();
        $service    = $this->getServiceDefinition();
        $definition = new MemcachedDefinition('default');

        $this->assertEmpty($service->getArguments());

        $config = $this->processConfiguration('memcached', ['host' => '127.0.0.1', 'port' => 6379]);

        $definition->configure($config, $service, $container);
        $this->assertCount(1, $service->getArguments());

        /** @var Definition $locker */
        $locker = $service->getArgument(0);
        $this->assertInstanceOf(Definition::class, $locker);
        $this->assertEquals('%ninja_mutex.locker_memcached_class%', $locker->getClass());
        $this->assertCount(1, $locker->getArguments());
        /** @var Definition $conn */
        $conn = $locker->getArgument(0);
        $this->assertInstanceOf(Definition::class, $conn);
        $this->assertEquals('%i_xarlie_mutex.memcached.connection.class%', $conn->getClass());
        $this->assertCount(0, $conn->getArguments());
        $this->assertFalse($conn->isPublic());
        $this->assertCount(1, $conn->getMethodCalls());

        $calls = $conn->getMethodCalls();

        $this->assertEquals('addServer', $calls[0][0]);
        $this->assertEquals([$config['host'], $config['port']], $calls[0][1]);
    }

    public function testConfigureLogger()
    {
        $container  = $this->getContainer();
        $service    = $this->getServiceDefinition();
        $definition = new MemcachedDefinition('default');

        $this->assertEmpty($service->getArguments());

        $container->setDefinition('logger', new Definition('%logger.class%'));

        $config = ['host' => '127.0.0.1', 'port' => 6379, 'logger' => 'logger'];
        $config = $this->processConfiguration('memcached', $config);
        $definition->configure($config, $service, $container);

        $this->assertCount(2, $service->getArguments());
        $this->assertEquals('%logger.class%', $service->getArgument(1)->getClass());
    }
}
