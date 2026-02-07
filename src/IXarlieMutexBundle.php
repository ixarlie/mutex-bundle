<?php declare(strict_types=1);

namespace IXarlie\MutexBundle;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
final class IXarlieMutexBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        // @formatter:off
        $definition->rootNode()
            ->children()
                ->arrayNode('factories')
                    ->defaultValue([])
                    ->scalarPrototype()->end()
                ->end() // factories
            ->end()
        ;
        // @formatter:on
    }

    /**
     * @param array<mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services.yaml');
        $container->import('../config/listeners.yaml');

        $this->registerFactories($container, $config);
    }

    /**
     * @param array<mixed> $config
     */
    private function registerFactories(ContainerConfigurator $container, array $config): void
    {
        $service = $container->services()->get('ixarlie_mutex.lock_executor');
        foreach ($config['factories'] as $serviceId) {
            $service->call('addLockFactory', [$serviceId, new Reference($serviceId)]);
        }
    }
}
