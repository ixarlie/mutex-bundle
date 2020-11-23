<?php

namespace Tests\DependencyInjection\Compiler;

use IXarlie\MutexBundle\DependencyInjection\Compiler\SymfonyServicesPass;
use IXarlie\MutexBundle\EventListener\MutexDecoratorListener;
use IXarlie\MutexBundle\EventListener\MutexExceptionListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class SymfonyServicesPassTest.
 */
final class SymfonyServicesPassTest extends TestCase
{
    public function testProcess(): void
    {
        // Prepare container
        $container = new ContainerBuilder();
        $decorator = new Definition(MutexDecoratorListener::class);
        $exception = new Definition(MutexExceptionListener::class);

        $container->setDefinition(MutexDecoratorListener::class, $decorator);
        $container->setDefinition(MutexExceptionListener::class, $exception);

        // Symfony services
        $container->setDefinition('translator', new Definition(TranslatorInterface::class));
        $container->setDefinition('security.token_storage', new Definition(TokenStorageInterface::class));

        static::assertCount(0, $decorator->getArguments());
        static::assertCount(0, $exception->getArguments());

        $pass = new SymfonyServicesPass();
        $pass->process($container);

        static::assertCount(1, $decorator->getArguments());
        static::assertCount(1, $exception->getArguments());

        static::assertInstanceOf(Reference::class, $decorator->getArgument(0));
        static::assertEquals('security.token_storage', (string) $decorator->getArgument(0));
        static::assertInstanceOf(Reference::class, $exception->getArgument(0));
        static::assertEquals('translator', (string) $exception->getArgument(0));
    }
}
