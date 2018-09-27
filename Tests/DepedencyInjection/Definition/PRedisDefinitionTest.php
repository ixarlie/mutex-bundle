<?php

namespace IXarlie\MutexBundle\Tests\DependencyInjection\Definition;

use IXarlie\MutexBundle\DependencyInjection\Definition\LockDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\PRedisDefinition;
use IXarlie\MutexBundle\Tests\Util\UtilTestTrait;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class PRedisDefinitionTest
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class PRedisDefinitionTest extends \PHPUnit_Framework_TestCase
{
    use UtilTestTrait;

    public function testInstanceOf()
    {
        $this->assertInstanceOf(LockDefinition::class, new PRedisDefinition('default'));
    }

    public function dataConfigurations()
    {
        return [
            [['connection' => 'tcp://127.0.0.1:6379']],
            [['connection' => ['host' => '127.0.0.1', 'port' => 6379]]]
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

        $this->assertEmpty($service->getArguments());

        $config     = $this->processConfiguration('predis', $config);
        $connection = isset($config['connection']['uri']) ? $config['connection']['uri'] : $config['connection'];
        $options    = $config['options'];

        $definition->createFactory($config, $service, $container);
        $this->assertCount(1, $service->getArguments());

        /** @var Definition $locker */
        $locker = $service->getArgument(0);
        $this->assertInstanceOf(Definition::class, $locker);
        $this->assertEquals('%ninja_mutex.locker_predis_class%', $locker->getClass());
        $this->assertCount(1, $locker->getArguments());
        /** @var Definition $conn */
        $conn = $locker->getArgument(0);
        $this->assertInstanceOf(Definition::class, $conn);
        $this->assertEquals('%i_xarlie_mutex.predis.connection.class%', $conn->getClass());
        $this->assertCount(2, $conn->getArguments());
        $this->assertFalse($conn->isPublic());

        $this->assertEquals($connection, $conn->getArgument(0));
        $this->assertEquals($options, $conn->getArgument(1));
    }

    public function testConfigureLogger()
    {
        $container  = $this->getContainer();
        $service    = $this->getServiceDefinition();
        $definition = new PRedisDefinition('default');

        $this->assertEmpty($service->getArguments());

        $container->setDefinition('logger', new Definition('%logger.class%'));

        $config = ['connection' => 'tcp://127.0.0.1:6379', 'logger' => 'logger'];
        $config = $this->processConfiguration('predis', $config);
        $definition->createFactory($config, $service, $container);

        $this->assertCount(2, $service->getArguments());
        $this->assertEquals('%logger.class%', $service->getArgument(1)->getClass());
    }
}
