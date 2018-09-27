<?php

namespace IXarlie\MutexBundle\Tests\DependencyInjection\Definition;

use IXarlie\MutexBundle\DependencyInjection\Definition\FlockDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\LockDefinition;
use IXarlie\MutexBundle\Tests\Util\UtilTestTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class FlockDefinitionTest
 */
class FlockDefinitionTest extends TestCase
{
    use UtilTestTrait;

    public function testInstanceOf()
    {
        $this->assertInstanceOf(LockDefinition::class, new FlockDefinition());
    }

    public function testConfigure()
    {
        $container  = $this->getContainer();
        $service    = $this->getServiceDefinition();
        $definition = new FlockDefinition();

        $this->assertEmpty($service->getArguments());

        $config = $this->processConfiguration('flock', ['cache_dir' => '/tmp/flock']);

        $definition->createFactory($container, $config);
        $this->assertCount(1, $service->getArguments());

        /** @var Definition $locker */
        $locker = $service->getArgument(0);
        $this->assertInstanceOf(Definition::class, $locker);
        $this->assertEquals('%ninja_mutex.locker_flock_class%', $locker->getClass());
        $this->assertCount(1, $locker->getArguments());
        $arg0 = $locker->getArgument(0);
        $this->assertEquals($config['cache_dir'], $arg0);
    }

    public function testConfigureLogger()
    {
        $container  = $this->getContainer();
        $service    = $this->getServiceDefinition();
        $definition = new FlockDefinition();

        $this->assertEmpty($service->getArguments());

        $container->setDefinition('logger', new Definition('%logger.class%'));

        $config = ['cache_dir' => '/tmp/flock', 'logger' => 'logger'];
        $config = $this->processConfiguration('flock', $config);
        $definition->createFactory($container, $config);

        $this->assertCount(2, $service->getArguments());
        $this->assertEquals('%logger.class%', $service->getArgument(1)->getClass());
    }
}
