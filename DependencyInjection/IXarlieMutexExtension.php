<?php

namespace IXarlie\MutexBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Class IXarlieMutexExtension
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class IXarlieMutexExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('lockers.yml');
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (isset($config['logger'])) {
            $container->setParameter('ixarlie_mutex.logger', $config['logger']);
        }
        if (isset($config['flock'])) {
            $container->setParameter('ixarlie_mutex.locker_flock.cache_dir', $config['flock']['cache_dir']);
        } else {
            $container->getParameterBag()->remove('ixarlie_mutex.locker_flock.cache_dir');
        }
        if (isset($config['memcache'])) {
            $container->setParameter('ixarlie_mutex.locker_memcache.client', $config['memcache']['client']);
        } else {
            $container->getParameterBag()->remove('ixarlie_mutex.locker_memcache.client');
        }
        if (isset($config['memcached'])) {
            $container->setParameter('ixarlie_mutex.locker_memcached.client', $config['memcached']['client']);
        } else {
            $container->getParameterBag()->remove('ixarlie_mutex.locker_memcached.client');
        }
        if (isset($config['redis'])) {
            $container->setParameter('ixarlie_mutex.locker_redis.client', $config['redis']['client']);
        } else {
            $container->getParameterBag()->remove('ixarlie_mutex.locker_redis.client');
        }
        // @TODO mysql
    }
}
