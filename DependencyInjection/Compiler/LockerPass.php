<?php

namespace IXarlie\MutexBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
        if ($cacheDir = $container->getParameter('i_xarlie_mutex.locker_flock.cache_dir')) {
            $locker = $container->getDefinition('ninja_mutex.locker_flock');
            $locker->replaceArgument(0, $cacheDir);
            $manager = $container->getDefinition('i_xarlie_mutex.locker_flock');
            $manager->replaceArgument(0, $locker);
        }
        if ($client = $container->getParameter('i_xarlie_mutex.locker_memcache.client')) {
            $locker = $container->getDefinition('ninja_mutex.locker_memcache');
            $client = $container->findDefinition($client);
            $locker->replaceArgument(0, $client);
            $manager = $container->getDefinition('i_xarlie_mutex.locker_memcache');
            $manager->replaceArgument(0, $locker);
        }
        if ($client = $container->getParameter('i_xarlie_mutex.locker_memcached.client')) {
            $locker = $container->getDefinition('ninja_mutex.locker.memcached');
            $client = $container->findDefinition($client);
            $locker->replaceArgument(0, $client);
            $manager = $container->getDefinition('i_xarlie_mutex.locker_memcached');
            $manager->replaceArgument(0, $locker);
        }
        if ($client = $container->getParameter('i_xarlie_mutex.locker_redis.client')) {
            $locker = $container->getDefinition('ninja_mutex.locker_redis');
            $client = $container->findDefinition($client);
            $locker->replaceArgument(0, $client);
            $manager = $container->getDefinition('i_xarlie_mutex.locker_redis');
            $manager->replaceArgument(0, $locker);
        }
        // @TODO mysql
    }
}
