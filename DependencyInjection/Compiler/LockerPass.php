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
            $class  = $container->getParameter('i_xarlie_mutex.redis.connection.class');
            /** @var \Redis $client */
            $client = new $class();
            $client->connect($config['host'], $config['port']);
            if (isset($config['password'])) {
                $client->auth($config['password']);
            }
            if (isset($config['database'])) {
                $client->select((int) $config['database']);
            }
            $locker = $container->getDefinition('ninja_mutex.locker_redis');
            $locker->replaceArgument(0, $client);
            $manager = $container->getDefinition('i_xarlie_mutex.locker_redis');
            $manager->addArgument($locker);
            $this->addLoggerService($config, $manager, $container);
        }
        if ($container->hasParameter('i_xarlie_mutex.locker_predis')) {
            $config = $container->getParameter('i_xarlie_mutex.locker_predis');
            $class = $container->getParameter('i_xarlie_mutex.predis.connection.class');
            /** @var \Predis\Client $client */
            $client = new $class($config);
            $locker = $container->getDefinition('ninja_mutex.locker_redis');
            $locker->replaceArgument(0, $client);
            $manager = $container->getDefinition('i_xarlie_mutex.locker_predis');
            $manager->addArgument($locker);
            $this->addLoggerService($config, $manager, $container);
        }

        if ($container->hasParameter('i_xarlie_mutex.locker_memcache')) {
            $config = $container->getParameter('i_xarlie_mutex.locker_memcache');
            $class  = $container->getParameter('i_xarlie_mutex.memcache.connection.class');
            /** @var \Memcache $client */
            $client = new $class();
            $client->addserver($config['host'], $config['port']);
            $locker = $container->getDefinition('ninja_mutex.locker_memcache');
            $locker->replaceArgument(0, $client);
            $manager = $container->getDefinition('i_xarlie_mutex.locker_memcache');
            $manager->addArgument($locker);
            $this->addLoggerService($config, $manager, $container);
        }
        if ($container->hasParameter('i_xarlie_mutex.locker_memcached')) {
            $config = $container->getParameter('i_xarlie_mutex.locker_memcached');
            $class  = $container->getParameter('i_xarlie_mutex.memcached.connection.class');
            /** @var \Memcached $client */
            $client = new $class();
            $client->addServer($config['host'], $config['port']);
            $locker = $container->getDefinition('ninja_mutex.locker_memcached');
            $locker->replaceArgument(0, $client);
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
