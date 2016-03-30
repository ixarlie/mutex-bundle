<?php

namespace IXarlie\MutexBundle\DependencyInjection;

use IXarlie\MutexBundle\DependencyInjection\Definition\LockDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
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

        $this->loadLockProviders($config, $container);
    }

    private function loadLockProviders(array $rootConfig, ContainerBuilder $container)
    {
        foreach ($rootConfig as $type => $config) {
            $definition = $this->getDefinitionLoader($type, $container);
            if ($definition) {
                $service = $this->getDefinitionService($type, $container);
                $definition->configure($config, $service, $container);
            }
        }
    }

    /**
     * @param string           $type
     * @param ContainerBuilder $container
     * @return LockDefinition
     */
    private function getDefinitionLoader($type, ContainerBuilder $container)
    {
        $class = sprintf('%s\Definition\%s', __NAMESPACE__, ucfirst($type) . 'Definition');
        if (class_exists($class)) {
            return new $class($type);
        }
        return;
    }

    /**
     * @param string           $type
     * @param ContainerBuilder $container
     * @return Definition
     */
    private function getDefinitionService($type, ContainerBuilder $container)
    {
        $serviceId    = 'i_xarlie_mutex.locker_' . $type;
        $managerClass = '%i_xarlie_mutex.lock_manager_class%';

        return $container->setDefinition($serviceId, new Definition($managerClass));
    }
}
