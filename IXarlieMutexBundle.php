<?php

namespace IXarlie\MutexBundle;

use Symfony\Component\DependencyInjection\Compiler\PassConfig;
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
        parent::build($container);
    }
}
