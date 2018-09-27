<?php

namespace IXarlie\MutexBundle\DependencyInjection;

use IXarlie\MutexBundle\DependencyInjection\Definition\LockDefinition;
use IXarlie\MutexBundle\EventListener\MutexDecoratorListener;
use IXarlie\MutexBundle\EventListener\MutexExceptionListener;
use IXarlie\MutexBundle\EventListener\MutexReleaseListener;
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

        $this->loadStoreProviders($container, $config);
        $this->loadRequestListener($container, $config);
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $rootConfig
     *
     * @return array    List of locker managers id to be registered in the request listener
     */
    private function loadStoreProviders(ContainerBuilder $container, array $rootConfig)
    {
        $providers = [];
        $default   = $rootConfig['default'];
        unset($rootConfig['default'], $rootConfig['request_listener']);

        foreach ($rootConfig as $type => $declarations) {
            foreach ($declarations as $name => $config) {
                $config['default'] = $default;
                if ($loader = $this->getDefinitionLoader($type)) {
                    // Register factory and createFactory its store using its decorator definition
                    $loader->createFactory($container, $config, $name);
                }
            }
        }

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
            return new $class();
        }

        return null;
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    private function loadRequestListener(ContainerBuilder $container, array $config)
    {
        if (isset($config['request_listener']['enabled']) && false === $config['request_listener']['enabled']) {
            // Default listener was disabled.
            return;
        }

        $definition = new Definition(MutexRequestListener::class);
        $container->setDefinition(MutexRequestListener::class, $definition);

        // Register as many lockers were registered in the configuration
        $factories = $container->findTaggedServiceIds('ixarlie_factory');
        foreach ($factories as $factoryId => $options) {
            $definition->addMethodCall('addFactory', [$factoryId, new Reference($factoryId)]);
        }

        $config = $config['request_listener'];

        // Register listener as soon as possible, default priority 255
        $definition->addTag(
            'kernel.event_listener',
            ['event' => KernelEvents::CONTROLLER, 'method' => 'onKernelController', 'priority' => $config['priority']]
        );
        $definition->addTag(
            'kernel.event_listener',
            ['event' => KernelEvents::TERMINATE, 'method' => 'onKernelTerminate']
        );

        // Configure MutexDecoratorListener
        $decoratorListener = new Definition(MutexDecoratorListener::class);
        $decoratorListener->addTag(
            'kernel.event_listener',
            [
                'event'    => KernelEvents::CONTROLLER,
                'method'   => 'onKernelController',
                'priority' => $config['priority'] + 1
            ]
        );
        $container->setDefinition(MutexDecoratorListener::class, $decoratorListener);
        if (isset($config['user_isolation']) && true === $config['user_isolation']) {

            if (!$container->hasDefinition('security.token_storage')) {
                throw new ServiceNotFoundException('security.token_storage', MutexDecoratorListener::class);
            }

            $decoratorListener->addArgument(new Reference('security.token_storage'));
        }

        $decoratorOptions = [
            'requestPlaceholder'   => $config['request_placeholder'],
            'httpExceptionCode'    => $config['http_exception']['code'],
            'httpExceptionMessage' => $config['http_exception']['message'],
        ];
        $decoratorListener->addMethodCall('setDecoratorOptions', [$decoratorOptions]);


        // Configure MutexExceptionListener
        $exceptionListener = new Definition(MutexExceptionListener::class);
        $exceptionListener->addTag(
            'kernel.event_listener',
            ['event' => KernelEvents::EXCEPTION, 'method' => 'onKernelException']
        );
        $container->setDefinition(MutexExceptionListener::class, $exceptionListener);

        if (isset($config['translator']) && true === $config['translator']) {

            if (!$container->hasDefinition('translator')) {
                throw new ServiceNotFoundException('translator', MutexExceptionListener::class);
            }

            $exceptionListener->addArgument(new Reference('translator'));
        }

        // Configure MutexReleaseListener
        $terminateListener = new Definition(MutexReleaseListener::class);
        $terminateListener->addTag(
            'kernel.event_listener',
            ['event' => KernelEvents::TERMINATE, 'method' => 'onKernelTerminate', 'priority' => -255]
        );
        $container->setDefinition(MutexReleaseListener::class, $terminateListener);
    }
}
