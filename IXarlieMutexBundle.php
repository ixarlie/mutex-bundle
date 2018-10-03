<?php

namespace IXarlie\MutexBundle;

use IXarlie\MutexBundle\DependencyInjection\Compiler\ControllerListenerPass;
use IXarlie\MutexBundle\DependencyInjection\Compiler\SymfonyServicesPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class IXarlieMutexBundle
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class IXarlieMutexBundle extends Bundle
{
    /**
     * @inheritdoc
     * @throws \ReflectionException
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new SymfonyServicesPass());
        $container->addCompilerPass(new ControllerListenerPass());
    }
}
