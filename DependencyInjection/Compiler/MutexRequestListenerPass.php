<?php

namespace IXarlie\MutexBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class MutexRequestListenerPass
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class MutexRequestListenerPass implements CompilerPassInterface
{
    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('i_xarlie_mutex.controller.listener')) {
            return;
        }

        $definition  = $container->getDefinition('i_xarlie_mutex.controller.listener');
        $methodCalls = $definition->getMethodCalls();
        foreach ($methodCalls as [$method, $value]) {
            if (!isset($value[0]) || !$value[0] instanceof Reference) {
                continue;
            }

            if (!$container->has((string) $value[0])) {
                $definition->removeMethodCall($method);
            }
        }
    }
}
