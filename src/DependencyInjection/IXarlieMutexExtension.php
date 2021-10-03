<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

/**
 * Class IXarlieMutexExtension
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class IXarlieMutexExtension extends Extension
{
    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('listeners.xml');
        $loader->load('services.xml');

        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $this->registerStrategies($container);
        $this->registerFactories($container, $config);
    }

    /**
     * @param ContainerBuilder $container
     */
    private function registerStrategies(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition('ixarlie_mutex.lock_executor');
        foreach ($container->findTaggedServiceIds('ixarlie_mutex.strategy') as $id => $tags) {
            $definition->addMethodCall('addLockStrategy', [new Reference($id)]);
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    private function registerFactories(ContainerBuilder $container, array $config): void
    {
        $definition = $container->getDefinition('ixarlie_mutex.lock_executor');
        foreach ($config['factories'] as $serviceId) {
            $definition->addMethodCall('addLockFactory', [new Reference($serviceId)]);
        }
    }
}
