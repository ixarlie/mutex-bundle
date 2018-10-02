<?php

namespace Tests;

use IXarlie\MutexBundle\DependencyInjection\Compiler\ControllerListenerPass;
use IXarlie\MutexBundle\DependencyInjection\Compiler\SymfonyDecoratorPass;
use IXarlie\MutexBundle\IXarlieMutexBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class IXarlieMutexBundleTest
 */
class IXarlieMutexBundleTest extends TestCase
{
    public function testInstanceOf()
    {
        $bundle = new IXarlieMutexBundle();

        static::assertInstanceOf(Bundle::class, $bundle);
    }

    public function testBuild()
    {
        $bundle    = new IXarlieMutexBundle();
        $container = new ContainerBuilder();

        $bundle->build($container);

        $config = $container->getCompilerPassConfig();
        $passes = $config->getBeforeOptimizationPasses();

        static::assertCount(6, $passes);

        $result = array_filter($passes, function (CompilerPassInterface $pass) {
            return $pass instanceof ControllerListenerPass;
        });
        static::assertCount(1, $result);

        $result = array_filter($passes, function (CompilerPassInterface $pass) {
            return $pass instanceof SymfonyDecoratorPass;
        });
        static::assertCount(1, $result);
    }
}
