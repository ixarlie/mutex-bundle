<?php

namespace IXarlie\MutexBundle\DependencyInjection\Definition;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class RedisDefinition
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class RedisDefinition extends LockDefinition
{
    /**
     * {@inheritdoc}
     */
    protected function getLocker(array $config, ContainerBuilder $container)
    {
        $locker = new Definition('%ninja_mutex.locker_redis_class%');
        
        return $locker;
    }

    /**
     * {@inheritdoc}
     */
    protected function getClient(array $config, ContainerBuilder $container)
    {
        $client = new Definition('%i_xarlie_mutex.redis.connection.class%');
        $client->addMethodCall('connect', [$config['host'], $config['port']]);
        if (isset($config['password'])) {
            $client->addMethodCall('auth', [$config['password']]);
        }
        if (isset($config['database'])) {
            $client->addMethodCall('select', [(int) $config['database']]);
        }
        
        return $client;
    }
}
