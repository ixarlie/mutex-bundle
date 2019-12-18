<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\Tests\DepedencyInjection\Compiler;

use IXarlie\MutexBundle\DependencyInjection\Compiler\MutexRequestListenerPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class MutexRequestListenerPassTest.
 */
class MutexRequestListenerPassTest extends TestCase
{
    public function testProcess()
    {
        $container = new ContainerBuilder();

        $definition = new Definition(MutexRequestListenerPass::class);
        $container->setDefinition('i_xarlie_mutex.controller.listener', $definition);

        $definition->addMethodCall('setTranslator', [new Reference('translator')]);
        $definition->addMethodCall('setTokenStorage', [new Reference('security.token_storage')]);

        static::assertCount(2, $definition->getMethodCalls());

        $compiler = new MutexRequestListenerPass();
        $compiler->process($container);

        static::assertCount(0, $definition->getMethodCalls());
    }
}
