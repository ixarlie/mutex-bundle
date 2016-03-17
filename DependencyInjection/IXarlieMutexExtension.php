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

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        if (isset($config['flock'])) {
            $container->setParameter('ixarlie_mutex.flock.cache_dir', $config['flock']['cache_dir']);
        }
        if (isset($config['memcache'])) {
            $container->setParameter('ixarlie_mutex.memcache.client', $config['memcache']['client']);
        }
        if (isset($config['memcached'])) {
            $container->setParameter('ixarlie_mutex.memcached.client', $config['memcached']['client']);
        }
        if (isset($config['redis'])) {
            $container->setParameter('ixarlie_mutex.redis.client', $config['redis']['client']);
        }
        // @TODO mysql
    }
}
