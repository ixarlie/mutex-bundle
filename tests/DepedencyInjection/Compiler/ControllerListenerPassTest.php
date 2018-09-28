<?php

namespace Tests\DepedencyInjection\Compiler;

use IXarlie\MutexBundle\DependencyInjection\Compiler\ControllerListenerPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ControllerListenerPassTest
 */
class ControllerListenerPassTest extends TestCase
{
    public function testInstanceOf()
    {
        $pass = new ControllerListenerPass();

        static::assertInstanceOf(CompilerPassInterface::class, $pass);
    }

    /**
     * @expectedException \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     */
    public function testMissingSensioControllerListener()
    {
        $pass      = new ControllerListenerPass();
        $container = $this->createContainerBuilder(1, false);

        $pass->process($container);
    }

    /**
     * @dataProvider dataPriorityProvider
     * @param int $priority
     * @param int $expectedPriority
     */
    public function testProcess($priority, $expectedPriority)
    {
        $pass      = new ControllerListenerPass();
        $container = $this->createContainerBuilder($priority, true);

        static::assertTrue($container->hasDefinition('ixarlie_mutex.controller.listener'));
        static::assertTrue($container->hasDefinition('sensio_framework_extra.controller.listener'));

        $mutexListener      = $container->getDefinition('ixarlie_mutex.controller.listener');
        $controllerListener = $container->getDefinition('sensio_framework_extra.controller.listener');

        static::assertTrue($mutexListener->hasTag('kernel.event_listener'));
        static::assertTrue($controllerListener->hasTag('kernel.event_subscriber'));

        $pass->process($container);

        static::assertFalse($controllerListener->hasTag('kernel.event_subscriber'));
        static::assertTrue($controllerListener->hasTag('kernel.event_listener'));

        $tags = $controllerListener->getTag('kernel.event_listener');

        static::assertEquals(KernelEvents::CONTROLLER, $tags[0]['event']);
        static::assertEquals('onKernelController', $tags[0]['method']);
        static::assertEquals($expectedPriority, $tags[0]['priority']);
    }

    /**
     * @return array
     */
    public function dataPriorityProvider()
    {
        return [
            [1, 6],
            [10, 15],
            [20, 25],
            [100, 105],
            [255, 260],
            [1000, 1005],
        ];
    }

    /**
     * @param int  $priority
     * @param bool $withSensio
     *
     * @return ContainerBuilder
     */
    private function createContainerBuilder($priority, $withSensio)
    {
        $container  = new ContainerBuilder();

        $definition = new Definition();
        $definition->addTag('kernel.event_listener', ['priority' => $priority]);
        $container->setDefinition('ixarlie_mutex.controller.listener', $definition);

        if ($withSensio) {
            $definition = new Definition();
            $definition->addTag('kernel.event_subscriber');
            $container->setDefinition('sensio_framework_extra.controller.listener', $definition);
        }

        return $container;
    }
}
