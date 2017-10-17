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
    protected function getLocker(array $config, ContainerBuilder $container)
    {
        $locker = new Definition('%ninja_mutex.locker_memcache_class%');
        
        return $locker;
    }

    /**
     * {@inheritdoc}
     */
    protected function getClient(array $config, ContainerBuilder $container)
    {
        $client = new Definition('%i_xarlie_mutex.memcache.connection.class%');
        $client->addMethodCall('addserver', [$config['host'], $config['port']]);
        
        return $client;
    }
}
