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
     * @inheritDoc
     */
    protected function getLocker(array $config, ContainerBuilder $container): Definition
    {
        $locker = new Definition('%ninja_mutex.locker_memcached_class%');

        return $locker;
    }

    /**
     * @inheritDoc
     */
    protected function getClient(array $config, ContainerBuilder $container): ?Definition
    {
        $client = new Definition('%i_xarlie_mutex.memcached.connection.class%');
        $client->addMethodCall('addServer', [$config['host'], $config['port']]);

        return $client;
    }
}
