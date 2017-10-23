<?php

namespace IXarlie\MutexBundle\DependencyInjection\Compiler;

use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class MutexRequestListenerPass
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class MutexRequestListenerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('i_xarlie_mutex.controller.listener')) {
            return;
        }
        
        $definition = $container->getDefinition('i_xarlie_mutex.controller.listener');

        /** @var Reference $readerReference */
        $readerReference = $definition->getArgument(0);
        if (!$container->has((string) $readerReference)) {
            $reader = new Definition(AnnotationReader::class);
            $container->setDefinition('annotation_reader', $reader);
            $definition->replaceArgument(0, $reader);
        }
        
        $methodCalls = $definition->getMethodCalls();
        foreach ($methodCalls as list($method, $value)) {
            if (!isset($value[0]) || !$value[0] instanceof Reference) {
                continue;
            }
            
            if (!$container->hasDefinition((string) $value[0])) {
                $definition->removeMethodCall($method);
            }
        }
    }
}
