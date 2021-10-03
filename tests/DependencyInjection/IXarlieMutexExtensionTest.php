<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\Tests\DependencyInjection;

use IXarlie\MutexBundle\DependencyInjection\IXarlieMutexExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

/**
 * Class IXarlieMutexExtensionTest
 */
final class IXarlieMutexExtensionTest extends TestCase
{
    public function testInstance(): void
    {
        self::assertInstanceOf(ExtensionInterface::class, new IXarlieMutexExtension());
    }

    public function testLoad(): void
    {
        $container = new ContainerBuilder();
        $extension = new IXarlieMutexExtension();
        $extension->load(['i_xarlie_mutex' => []], $container);

        self::assertTrue($container->hasDefinition('ixarlie_mutex.controller.listener'));
        self::assertTrue($container->hasDefinition('ixarlie_mutex.terminate.listener'));
        self::assertTrue($container->hasDefinition('ixarlie_mutex.lock_executor'));
        self::assertTrue($container->hasDefinition('ixarlie_mutex.naming_strategy.default'));
        self::assertTrue($container->hasDefinition('ixarlie_mutex.naming_strategy.user_isolation'));
        self::assertTrue($container->hasDefinition('ixarlie_mutex.lock_strategy.block'));
        self::assertTrue($container->hasDefinition('ixarlie_mutex.lock_strategy.queue'));
        self::assertTrue($container->hasDefinition('ixarlie_mutex.lock_strategy.force'));
        self::assertTrue($container->hasAlias('ixarlie_mutex.naming_strategy'));

        $definition = $container->getDefinition('ixarlie_mutex.lock_executor');
        self::assertCount(3, $definition->getMethodCalls());
    }

    public function testLoadWithFactories(): void
    {
        $container = new ContainerBuilder();
        $extension = new IXarlieMutexExtension();
        $extension->load(['i_xarlie_mutex' => [
            'factories' => [
                'lock.default.factory',
            ],
        ]], $container);

        self::assertTrue($container->hasDefinition('ixarlie_mutex.controller.listener'));
        self::assertTrue($container->hasDefinition('ixarlie_mutex.terminate.listener'));
        self::assertTrue($container->hasDefinition('ixarlie_mutex.lock_executor'));
        self::assertTrue($container->hasDefinition('ixarlie_mutex.naming_strategy.default'));
        self::assertTrue($container->hasDefinition('ixarlie_mutex.naming_strategy.user_isolation'));
        self::assertTrue($container->hasDefinition('ixarlie_mutex.lock_strategy.block'));
        self::assertTrue($container->hasDefinition('ixarlie_mutex.lock_strategy.queue'));
        self::assertTrue($container->hasDefinition('ixarlie_mutex.lock_strategy.force'));
        self::assertTrue($container->hasAlias('ixarlie_mutex.naming_strategy'));

        $definition = $container->getDefinition('ixarlie_mutex.lock_executor');
        self::assertCount(4, $definition->getMethodCalls());
    }
}
