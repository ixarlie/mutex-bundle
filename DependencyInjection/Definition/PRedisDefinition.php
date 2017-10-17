<?php

namespace IXarlie\MutexBundle\DependencyInjection\Definition;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class PRedisDefinition
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class PRedisDefinition extends LockDefinition
{
    /**
     * {@inheritdoc}
     */
    protected function getLocker(array $config, ContainerBuilder $container)
    {
        $locker = new Definition('%ninja_mutex.locker_predis_class%');

        return $locker;
    }

    /**
     * {@inheritdoc}
     */
    protected function getClient(array $config, ContainerBuilder $container)
    {
        $client = new Definition('%i_xarlie_mutex.predis.connection.class%');
        $parameters = $options = null;
        if (isset($config['connection']['uri'])) {
            $parameters = $config['connection']['uri'];
        } elseif (is_array($config['connection'])) {
            $parameters = $config['connection'];
        }
        if (isset($config['options'])) {
            $options = $config['options'];
        }
        $client->setArguments([$parameters, $options]);
        
        return $client;
    }
}
