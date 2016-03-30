<?php

namespace IXarlie\MutexBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

/**
 * Class LockerPass
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class LockerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if ($container->hasParameter('i_xarlie_mutex.locker_flock')) {
            $config = $container->getParameter('i_xarlie_mutex.locker_flock');
            $locker = $container->getDefinition('ninja_mutex.locker_flock');
            $locker->replaceArgument(0, $config['cache_dir']);
            $manager = $container->getDefinition('i_xarlie_mutex.locker_flock');
            $manager->addArgument($locker);
            $this->addLoggerService($config, $manager, $container);
        }
        if ($container->hasParameter('i_xarlie_mutex.locker_redis')) {
            $config = $container->getParameter('i_xarlie_mutex.locker_redis');

            $connClass  = '%i_xarlie_mutex.redis.connection.class%';
            $connDef    = new Definition($connClass);
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
            $locker  = $container->getDefinition('ninja_mutex.locker_redis');
            $locker->replaceArgument(0, $connDef);
            $manager = $container->getDefinition('i_xarlie_mutex.locker_redis');
            $manager->addArgument($locker);
            $this->addLoggerService($config, $manager, $container);
        }
        if ($container->hasParameter('i_xarlie_mutex.locker_predis')) {
            $config = $container->getParameter('i_xarlie_mutex.locker_predis');

            $connClass = '%i_xarlie_mutex.predis.connection.class%';
            $connDef   = new Definition($connClass);
            $connDef->setPublic(false);
            $connDef->setArguments($config);
            $locker  = $container->getDefinition('ninja_mutex.locker_predis');
            $locker->replaceArgument(0, $connDef);
            $manager = $container->getDefinition('i_xarlie_mutex.locker_predis');
            $manager->addArgument($locker);
            $this->addLoggerService($config, $manager, $container);
        }
        if ($container->hasParameter('i_xarlie_mutex.locker_memcache')) {
            $config = $container->getParameter('i_xarlie_mutex.locker_memcache');

            $connClass  = '%i_xarlie_mutex.memcache.connection.class%';
            $connDef    = new Definition($connClass);
            $connParams = [
                $config['host'],
                $config['port']
            ];
            $connDef->setPublic(false);
            $connDef->addMethodCall('addserver', $connParams);
            $locker  = $container->getDefinition('ninja_mutex.locker_memcache');
            $locker->replaceArgument(0, $connDef);
            $manager = $container->getDefinition('i_xarlie_mutex.locker_memcache');
            $manager->addArgument($locker);
            $this->addLoggerService($config, $manager, $container);
        }
        if ($container->hasParameter('i_xarlie_mutex.locker_memcached')) {
            $config = $container->getParameter('i_xarlie_mutex.locker_memcached');

            $connClass  = '%i_xarlie_mutex.memcached.connection.class%';
            $connDef    = new Definition($connClass);
            $connParams = [
                $config['host'],
                $config['port']
            ];
            $connDef->addMethodCall('addServer', $connParams);
            $locker  = $container->getDefinition('ninja_mutex.locker_memcached');
            $locker->replaceArgument(0, $connDef);
            $manager = $container->getDefinition('i_xarlie_mutex.locker_memcached');
            $manager->replaceArgument(0, $locker);
            $this->addLoggerService($config, $manager, $container);
        }
        // @TODO mysql
    }

    /**
     * @param array            $config
     * @param Definition       $definition
     * @param ContainerBuilder $container
     */
    private function addLoggerService($config, Definition $definition, ContainerBuilder $container)
    {
        if (isset($config['logger'])) {
            $definition->addArgument($container->findDefinition($config['logger']));
        }
    }
}
