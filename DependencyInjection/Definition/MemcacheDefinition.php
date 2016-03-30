<?php

namespace IXarlie\MutexBundle\DependencyInjection\Definition;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class MemcacheDefinition
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class MemcacheDefinition extends LockDefinition
{
    /**
     * {@inheritdoc}
     */
    public function configure(array $config, Definition $service, ContainerBuilder $container)
    {
        $connClass = '%i_xarlie_mutex.memcache.connection.class%';
        $connDef   = new Definition($connClass);
        $connParams = [
            $config['host'],
            $config['port']
        ];
        $connDef->setPublic(false);
        $connDef->addMethodCall('addserver', $connParams);

        $lockerClass = '%ninja_mutex.locker_memcache_class%';
        $lockerDef   = new Definition($lockerClass);
        $lockerDef->addArgument($connDef);

        $service->addArgument($lockerDef);
        $this->addLoggerService($config, $service, $container);
    }
}
