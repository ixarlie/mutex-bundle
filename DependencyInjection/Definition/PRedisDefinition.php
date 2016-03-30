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
    public function configure(array $config, Definition $service, ContainerBuilder $container)
    {
        $connClass = '%i_xarlie_mutex.predis.connection.class%';
        $connDef   = new Definition($connClass);
        $connDef->setArguments($config);
        $connDef->setPublic(false);

        $lockerClass = '%ninja_mutex.locker_predis_class%';
        $lockerDef   = new Definition($lockerClass);
        $lockerDef->addArgument($connDef);

        $service->addArgument($lockerDef);
        $this->addLoggerService($config, $service, $container);
    }
}
