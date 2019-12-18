<?php

namespace IXarlie\MutexBundle\Tests\DependencyInjection\Definition;

use IXarlie\MutexBundle\DependencyInjection\Definition\FlockDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\LockDefinition;
use IXarlie\MutexBundle\Tests\Util\UtilTestTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class FlockDefinitionTest
 */
class FlockDefinitionTest extends TestCase
{
    use UtilTestTrait;

    public function testInstanceOf()
    {
        static::assertInstanceOf(LockDefinition::class, new FlockDefinition('default'));
    }

    public function testConfigure()
    {
        $container  = $this->getContainer();
        $service    = $this->getServiceDefinition();
        $definition = new FlockDefinition('default');

        static::assertEmpty($service->getArguments());

        $config = $this->processConfiguration('flock', ['cache_dir' => '/tmp/flock']);

        $definition->configure($config, $service, $container);
        static::assertCount(1, $service->getArguments());

        /** @var Definition $locker */
        $locker = $service->getArgument(0);
        static::assertInstanceOf(Definition::class, $locker);
        static::assertEquals('%ninja_mutex.locker_flock_class%', $locker->getClass());
        static::assertCount(1, $locker->getArguments());
        $arg0 = $locker->getArgument(0);
        static::assertEquals($config['cache_dir'], $arg0);
    }

    public function testConfigureLogger()
    {
        $container  = $this->getContainer();
        $service    = $this->getServiceDefinition();
        $definition = new FlockDefinition('default');

        static::assertEmpty($service->getArguments());

        $container->setDefinition('logger', new Definition('%logger.class%'));

        $config = ['cache_dir' => '/tmp/flock', 'logger' => 'logger'];
        $config = $this->processConfiguration('flock', $config);
        $definition->configure($config, $service, $container);

        static::assertCount(2, $service->getArguments());
        static::assertInstanceOf(Reference::class, $service->getArgument(1));
        static::assertEquals('logger', (string) $service->getArgument(1));
    }
}
