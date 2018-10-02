<?php

namespace IXarlie\MutexBundle\DependencyInjection\Compiler;

use IXarlie\MutexBundle\EventListener\MutexDecoratorListener;
use IXarlie\MutexBundle\EventListener\MutexExceptionListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class SymfonyDecoratorPass
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class SymfonyDecoratorPass implements CompilerPassInterface
{
    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition(MutexExceptionListener::class)) {
            $listener = $container->getDefinition(MutexExceptionListener::class);
            if ($container->hasDefinition('translator')) {
                $listener->addArgument(new Reference('translator'));
            }
            if ($container->hasDefinition('security.token_storage')) {
                $listener->addArgument(new Reference('security.token_storage'));
            }
        }

        if ($container->hasDefinition(MutexDecoratorListener::class)) {
            $listener = $container->getDefinition(MutexDecoratorListener::class);
            if ($container->hasDefinition('security.token_storage')) {
                $listener->addArgument(new Reference('security.token_storage'));
            }
        }
    }
}
