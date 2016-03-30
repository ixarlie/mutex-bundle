<?php

namespace IXarlie\MutexBundle\DependencyInjection\Definition;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class MemcachedDefinition
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class MemcachedDefinition extends LockDefinition
{
    /**
     * {@inheritdoc}
     */
    public function configure(array $config, Definition $service, ContainerBuilder $container)
    {
        // redis class
        $connClass = '%i_xarlie_mutex.memcached.connection.class%';
        $connDef   = new Definition($connClass);
        $connParams = [
            $config['host'],
            $config['port']
        ];
        $connDef->setPublic(false);
        $connDef->addMethodCall('addServer', $connParams);

        $lockerClass = '%ninja_mutex.locker_memcached_class%';
        $lockerDef   = new Definition($lockerClass);
        $lockerDef->addArgument($connDef);

        $service->addArgument($lockerDef);
        $this->addLoggerService($config, $service, $container);
    }
}
