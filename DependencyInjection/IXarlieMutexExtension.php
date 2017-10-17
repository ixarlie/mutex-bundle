<?php

namespace IXarlie\MutexBundle\DependencyInjection;

use Doctrine\Common\Annotations\AnnotationReader;
use IXarlie\MutexBundle\DependencyInjection\Definition\LockDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;
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
        // Get base definition of services and lockers
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('lockers.yml');
        $loader->load('services.yml');

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
                    $serviceId    = self::getLockerManagerId($type, $name);
                    $service      = $container->setDefinition(
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
        if (!$container->hasDefinition('i_xarlie_mutex.controller.listener')) {
            return;
        }

        $definition = $container->getDefinition('i_xarlie_mutex.controller.listener');

        // If there is no annotation_reader service, create the service
        if (!$container->has('annotation_reader')) {
            $reader = new Definition(AnnotationReader::class);
            $container->setDefinition('annotation_reader', $reader);
            $definition->replaceArgument(0, $reader);
        }

        // Register as many locker were registered in the configuration
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
        
        if (isset($config['translator']) &&
            true === $config['translator'] &&
            $container->hasDefinition('translator')
        ) {
            $definition->addMethodCall('setTranslator', [new Reference('translator')]);
        }

        if (isset($config['user_isolation']) &&
            true === $config['user_isolation'] &&
            $container->hasDefinition('security.token_storage')
        ) {
            $definition->addMethodCall('setTokenStorage', [new Reference('security.token_storage')]);
        }
    }
}
