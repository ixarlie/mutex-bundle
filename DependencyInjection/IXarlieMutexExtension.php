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

        foreach ($config as $name => $lockConfig) {
            $container->setParameter('i_xarlie_mutex.locker_' . $name, $lockConfig);
        }
    }
}
