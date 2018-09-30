<?php

namespace IXarlie\MutexBundle\DependencyInjection;

use IXarlie\MutexBundle\DependencyInjection\Definition\CombinedDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\CustomDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\FlockDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\LockDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\MemcachedDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\RedisDefinition;
use IXarlie\MutexBundle\DependencyInjection\Definition\SemaphoreDefinition;
use IXarlie\MutexBundle\EventListener\MutexDecoratorListener;
use IXarlie\MutexBundle\EventListener\MutexExceptionListener;
use IXarlie\MutexBundle\EventListener\MutexReleaseListener;
use IXarlie\MutexBundle\EventListener\MutexRequestListener;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Lock\Store\FlockStore;
use Symfony\Component\Lock\Store\MemcachedStore;
use Symfony\Component\Lock\Store\RedisStore;
use Symfony\Component\Lock\Store\SemaphoreStore;

/**
 * Class IXarlieMutexExtension
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class IXarlieMutexExtension extends Extension
{
    /**
     * @var LockDefinition[]
     */
    private $definitions;

    /**
     * IXarlieMutexExtension constructor.
     */
    public function __construct()
    {
        $definitions = [
            new FlockDefinition(),
            new SemaphoreDefinition(),
            new MemcachedDefinition(),
            new RedisDefinition(),
            new CombinedDefinition(),
            new CustomDefinition(),
        ];
        /** @var LockDefinition $definition */
        foreach ($definitions as $definition) {
            $this->definitions[$definition->getName()] = $definition;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        // Load parameters
        $container->setParameter('ixarlie_mutex.flock_store.class', FlockStore::class);
        $container->setParameter('ixarlie_mutex.semaphore_store.class', SemaphoreStore::class);
        $container->setParameter('ixarlie_mutex.memcached_store.class', MemcachedStore::class);
        $container->setParameter('ixarlie_mutex.redis_store.class', RedisStore::class);

        $configuration = new Configuration();
        $config        = $this->processConfiguration($configuration, $configs);

        $this->loadServices($container, $config);
        $this->loadRequestListener($container, $config);
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $rootConfig
     */
    private function loadServices(ContainerBuilder $container, array $rootConfig)
    {
        $default = $rootConfig['default'];
        unset($rootConfig['default'], $rootConfig['request_listener']);

        $factories = 0;
        foreach ($rootConfig as $type => $declarations) {
            foreach ($declarations as $name => $config) {
                $config['default'] = $default;

                if (!isset($this->definitions[$type])) {
                    throw new \RuntimeException('Cannot find definition class for type ' . $type);
                }

                // Register factory and createFactory its store using its decorator definition
                $this->definitions[$type]->createFactory($container, $config, $name);
                $factories++;
            }
        }

        if (!$container->hasAlias('ixarlie_mutex.default_factory') && $factories > 0) {
            throw new LogicException(sprintf(
                '%s is not a valid default factory name value. Use type.name form',
                $default
            ));
        }
    }

    /**
     * @param ContainerBuilder $container
     * @param array            $config
     */
    private function loadRequestListener(ContainerBuilder $container, array $config)
    {
        if (false === $config['request_listener']['enabled']) {
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

        if ($container->hasAlias('ixarlie_mutex.default_factory')) {
            $definition->addMethodCall(
                'addFactory',
                ['ixarlie_mutex.default_factory', new Reference($container->getAlias('ixarlie_mutex.default_factory'))]
            );
        }

        $config = $config['request_listener'];

        // Register listener as soon as possible, default priority 255
        $definition->addTag(
            'kernel.event_listener',
            ['event' => KernelEvents::CONTROLLER, 'method' => 'onKernelController', 'priority' => $config['priority']]
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

        if ($container->hasDefinition('security.token_storage')) {
            $decoratorListener->addArgument(new Reference('security.token_storage'));
        }

        // Configure MutexExceptionListener
        $exceptionListener = new Definition(MutexExceptionListener::class);
        $exceptionListener->addTag(
            'kernel.event_listener',
            ['event' => KernelEvents::EXCEPTION, 'method' => 'onKernelException', 'priority' => 255]
        );
        $container->setDefinition(MutexExceptionListener::class, $exceptionListener);

        if ($container->hasDefinition('translator')) {
            $exceptionListener->addArgument(new Reference('translator'));
        }

        // Configure MutexReleaseListener
        if ($config['autorelease']) {
            $terminateListener = new Definition(MutexReleaseListener::class);
            $terminateListener->addTag(
                'kernel.event_listener',
                ['event' => KernelEvents::TERMINATE, 'method' => 'onKernelTerminate', 'priority' => -255]
            );
            $container->setDefinition(MutexReleaseListener::class, $terminateListener);
        }
    }
}
