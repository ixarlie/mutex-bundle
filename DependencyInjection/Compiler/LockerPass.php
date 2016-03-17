<?php

namespace IXarlie\MutexBundle\DependencyInjection\Compiler;

use IXarlie\MutexBundle\Model\LockerManager;
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
        // @TODO want to get the logger service nicer
        $logger = null;
        if ($container->hasDefinition('logger')) {
            $logger = $container->findDefinition('logger');
        }
        if ($container->hasParameter('ixarlie_mutex.flock.cache_dir')) {
            $locker = $container->getDefinition('ninja_mutex.locker.flock');
            $locker->replaceArgument(0, $container->getParameter('ixarlie_mutex.flock.cache_dir'));
            $container->setDefinition(
                'ixarlie_mutex.locker.flock',
                new Definition(LockerManager::class, [$locker, $logger])
            );
        }
        if ($container->hasParameter('ixarlie_mutex.memcache.client')) {
            $locker = $container->getDefinition('ninja_mutex.locker.memcache');
            $client = $container->findDefinition($container->getParameter('ixarlie_mutex.memcache.client'));
            $locker->replaceArgument(0, $client);

            $container->setDefinition(
                'ixarlie_mutex.locker.memcache',
                new Definition(LockerManager::class, [$locker, $logger])
            );
        }
        if ($container->hasParameter('ixarlie_mutex.memcached.client')) {
            $locker = $container->getDefinition('ninja_mutex.locker.memcached');
            $client = $container->findDefinition($container->getParameter('ixarlie_mutex.memcached.client'));
            $locker->replaceArgument(0, $client);

            $container->setDefinition(
                'ixarlie_mutex.locker.memcached',
                new Definition(LockerManager::class, [$locker, $logger])
            );
        }
        if ($container->hasParameter('ixarlie_mutex.redis.client')) {
            $locker = $container->getDefinition('ninja_mutex.locker.redis');
            $client = $container->findDefinition($container->getParameter('ixarlie_mutex.redis.client'));
            $locker->replaceArgument(0, $client);

            $container->setDefinition(
                'ixarlie_mutex.locker.redis',
                new Definition(LockerManager::class, [$locker, $logger])
            );
        }
        // @TODO mysql
    }
}
