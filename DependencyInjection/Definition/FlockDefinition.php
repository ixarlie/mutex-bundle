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
     * {@inheritdoc}
     */
    public function configure(array $config, Definition $service, ContainerBuilder $container)
    {
        $lockerClass = '%ninja_mutex.locker_flock_class%';
        $lockerDef   = new Definition($lockerClass);
        $lockerDef->addArgument($config['cache_dir']);

        $service->addArgument($lockerDef);
        $this->addLoggerService($config, $service, $container);
    }
}
