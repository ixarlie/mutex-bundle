<?php

namespace IXarlie\MutexBundle\DependencyInjection\Definition;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class FlockDefinition
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class FlockDefinition extends LockDefinition
{
    /**
     * @inheritDoc
     */
    protected function getLocker(array $config, ContainerBuilder $container): Definition
    {
        $locker = new Definition('%ninja_mutex.locker_flock_class%');
        $locker->addArgument($config['cache_dir']);

        return $locker;
    }

    /**
     * @inheritDoc
     */
    protected function getClient(array $config, ContainerBuilder $container): ?Definition
    {
        return null;
    }
}
