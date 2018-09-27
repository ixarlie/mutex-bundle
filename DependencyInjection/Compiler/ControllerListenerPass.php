<?php

namespace IXarlie\MutexBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class ControllerListenerPass.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class ControllerListenerPass implements CompilerPassInterface
{
    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ixarlie_mutex.controller.listener')) {
            return;
        }

        // Boost ControllerListener priority to be able get configurations
        if (!$container->hasDefinition('sensio_framework_extra.controller.listener')) {
            throw new ServiceNotFoundException(
                'sensio_framework_extra.controller.listener',
                'ixarlie_mutex.controller.listener'
            );
        }

        $listener = $container->getDefinition('ixarlie_mutex.controller.listener');
        $tags     = $listener->getTag('kernel.event_listener');
        $priority = isset($tags[0]['priority']) ? $tags[0]['priority'] : null;

        if (null === $priority) {
            return;
        }

        // ControllerListener should run before our MutexRequestListener
        $listener            = $container->getDefinition('sensio_framework_extra.controller.listener');
        $listener->clearTag('kernel.event_subscriber');
        $options = ['event' => KernelEvents::CONTROLLER, 'method' => 'onKernelController', 'priority' => $priority + 5];
        $listener->addTag('kernel.event_listener', $options);
    }
}
