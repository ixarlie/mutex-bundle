<?php

namespace IXarlie\MutexBundle;

use IXarlie\MutexBundle\DependencyInjection\Compiler\MutexRequestListenerPass;
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
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new MutexRequestListenerPass());
        
        parent::build($container);
    }
}
