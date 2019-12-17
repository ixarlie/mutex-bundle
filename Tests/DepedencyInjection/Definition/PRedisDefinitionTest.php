<?php

namespace IXarlie\MutexBundle\Tests\DependencyInjection\Definition;

use IXarlie\MutexBundle\DependencyInjection\Definition\LockDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\PRedisDefinition;
use IXarlie\MutexBundle\Tests\Util\UtilTestTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class PRedisDefinitionTest
 */
class PRedisDefinitionTest extends TestCase
{
    use UtilTestTrait;

    public function testInstanceOf()
    {
        static::assertInstanceOf(LockDefinition::class, new PRedisDefinition('default'));
    }

    public function dataConfigurations()
    {
        return [
            [['connection' => 'tcp://127.0.0.1:6379']],
            [['connection' => ['host' => '127.0.0.1', 'port' => 6379]]],
        ];
    }

    /**
     * @dataProvider dataConfigurations
     *
     * @param array $config
     */
    public function testConfigure(array $config)
    {
        $container  = $this->getContainer();
        $service    = $this->getServiceDefinition();
        $definition = new PRedisDefinition('default');

        static::assertEmpty($service->getArguments());

        $config     = $this->processConfiguration('predis', $config);
        $connection = isset($config['connection']['uri']) ? $config['connection']['uri'] : $config['connection'];
        $options    = $config['options'];

        $definition->configure($config, $service, $container);
        static::assertCount(1, $service->getArguments());

        /** @var Definition $locker */
        $locker = $service->getArgument(0);
        static::assertInstanceOf(Definition::class, $locker);
        static::assertEquals('%ninja_mutex.locker_predis_class%', $locker->getClass());
        static::assertCount(1, $locker->getArguments());
        /** @var Definition $conn */
        $conn = $locker->getArgument(0);
        static::assertInstanceOf(Definition::class, $conn);
        static::assertEquals('%i_xarlie_mutex.predis.connection.class%', $conn->getClass());
        static::assertCount(2, $conn->getArguments());
        static::assertFalse($conn->isPublic());

        static::assertEquals($connection, $conn->getArgument(0));
        static::assertEquals($options, $conn->getArgument(1));
    }

    public function testConfigureLogger()
    {
        $container  = $this->getContainer();
        $service    = $this->getServiceDefinition();
        $definition = new PRedisDefinition('default');

        static::assertEmpty($service->getArguments());

        $container->setDefinition('logger', new Definition('%logger.class%'));

        $config = ['connection' => 'tcp://127.0.0.1:6379', 'logger' => 'logger'];
        $config = $this->processConfiguration('predis', $config);
        $definition->configure($config, $service, $container);

        static::assertCount(2, $service->getArguments());
        static::assertInstanceOf(Reference::class, $service->getArgument(1));
        static::assertEquals('logger', (string) $service->getArgument(1));
    }
}
