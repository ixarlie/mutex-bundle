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
    public function configure(array $config, Definition $service, ContainerBuilder $container) {
        $connClass = '%i_xarlie_mutex.redis.connection.class%';
        $connDef   = new Definition($connClass);
        $connParams = [
            $config['host'],
            $config['port']
        ];
        $connDef->setPublic(false);
        $connDef->addMethodCall('connect', $connParams);
        if (isset($config['password'])) {
            $connDef->addMethodCall('auth', [$config['password']]);
        }
        if (isset($config['database'])) {
            $connDef->addMethodCall('select', [(int) $config['database']]);
        }

        $lockerClass = '%ninja_mutex.locker_redis_class%';
        $lockerDef   = new Definition($lockerClass);
        $lockerDef->addArgument($connDef);

        $service->addArgument($lockerDef);
        $this->addLoggerService($config, $service, $container);
    }
}
