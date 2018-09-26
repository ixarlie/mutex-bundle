<?php

namespace IXarlie\MutexBundle\DependencyInjection;

use IXarlie\MutexBundle\DependencyInjection\Definition\LockDefinition;
use IXarlie\MutexBundle\EventListener\MutexRequestListener;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\KernelEvents;

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
        // Get base definition of services and lockers
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('lockers.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $providers = $this->loadLockProviders($config, $container);
        $this->loadRequestListener($config, $providers, $container);
    }

    /**
     * @param string $type
     * @param string $name
     *
     * @return string
     */
    public static function getLockerManagerId($type = null, $name = null)
    {
        $base = 'i_xarlie_mutex.locker';
        if ($type) {
            $base .= '_' . $type;
        }
        if ($name) {
            $base .= '.' . $name;
        }
        return $base;
    }

    /**
     * @param array            $rootConfig
     * @param ContainerBuilder $container
     *
     * @return array    List of locker managers id to be registered in the request listener
     */
    private function loadLockProviders(array $rootConfig, ContainerBuilder $container)
    {
        $providers = [];
        $default   = $rootConfig['default'];
        unset($rootConfig['default'], $rootConfig['request_listener']);
        foreach ($rootConfig as $type => $declarations) {
            foreach ($declarations as $name => $config) {
                if ($definition = $this->getDefinitionLoader($type)) {

                    // Register lock manager and configure its locker using its decorator definition
                    $serviceId = self::getLockerManagerId($type, $name);
                    $service   = $container->setDefinition(
                        $serviceId,
                        new Definition('%i_xarlie_mutex.lock_manager_class%')
                    );
                    $definition->configure($config, $service, $container);

                    $providers[] = $serviceId;
                }
            }
        }

        $aliasId   = self::getLockerManagerId();
        $serviceId = self::getLockerManagerId($default);
        if (!$container->hasDefinition($serviceId)) {
            throw new ServiceNotFoundException($serviceId, $aliasId);
        }
        $container->setAlias($aliasId, $serviceId);
        $providers[] = $aliasId;


        return $providers;
    }

    /**
     * @param string $type
     *
     * @return LockDefinition
     */
    private function getDefinitionLoader($type)
    {
        $class = sprintf('%s\Definition\%s', __NAMESPACE__, ucfirst($type) . 'Definition');
        if (class_exists($class)) {
            return new $class($type);
        }
        return null;
    }

    /**
     * @param array            $config
     * @param Definition[]     $providers
     * @param ContainerBuilder $container
     */
    private function loadRequestListener(array $config, array $providers, ContainerBuilder $container)
    {
        if (isset($config['request_listener']['enabled']) && false === $config['request_listener']['enabled']) {
            // Default listener was disabled.
            return;
        }

        $definition = new Definition(MutexRequestListener::class);
        $definition->addArgument(new Reference('annotation_reader'));

        // Register as many lockers were registered in the configuration
        foreach ($providers as $providerId) {
            $definition->addMethodCall('addLockerManager', [$providerId, new Reference($providerId)]);
        }

        // Configure listener with bundle configuration
        if (!isset($config['request_listener'])) {
            return;
        }

        $config = $config['request_listener'];

        $definition->addMethodCall(
            'setHttpExceptionOptions',
            [$config['http_exception']['message'], $config['http_exception']['code']]
        );

        if (isset($config['queue_timeout'])) {
            $definition->addMethodCall('setMaxQueueTimeout', [(int) $config['queue_timeout']]);
        }

        if (isset($config['queue_max_try'])) {
            $definition->addMethodCall('setMaxQueueTry', [(int) $config['queue_max_try']]);
        }

        if (isset($config['translator']) && true === $config['translator']) {
            $definition->addMethodCall('setTranslator', [new Reference('translator')]);
        }

        if (isset($config['user_isolation']) && true === $config['user_isolation']) {
            $definition->addMethodCall('setTokenStorage', [new Reference('security.token_storage')]);
        }

        if (isset($config['request_placeholder']) && true === $config['request_placeholder']) {
            $definition->addMethodCall('setRequestPlaceholder', [true]);
        }

        // Register listener as soon as possible, default priority 255
        $definition->addTag(
            'kernel.event_listener',
            ['event' => KernelEvents::CONTROLLER, 'method' => 'onKernelController', 'priority' => $config['priority']]
        );
        $definition->addTag(
            'kernel.event_listener',
            ['event' => KernelEvents::TERMINATE, 'method' => 'onKernelTerminate']
        );

        $container->setDefinition('i_xarlie_mutex.controller.listener', $definition);
    }
}
