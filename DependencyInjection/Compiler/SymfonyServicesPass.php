<?php

namespace IXarlie\MutexBundle\DependencyInjection\Compiler;

use IXarlie\MutexBundle\EventListener\MutexDecoratorListener;
use IXarlie\MutexBundle\EventListener\MutexExceptionListener;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class SymfonyServicesPass
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class SymfonyServicesPass implements CompilerPassInterface
{
    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasDefinition(MutexExceptionListener::class)) {
            $listener = $container->getDefinition(MutexExceptionListener::class);
            if ($container->hasDefinition('translator') || $container->hasAlias('translator')) {
                $listener->addArgument(new Reference('translator'));
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
