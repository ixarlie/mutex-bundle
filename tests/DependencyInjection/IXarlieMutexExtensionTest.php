<?php

namespace Tests\DependencyInjection;

use IXarlie\MutexBundle\DependencyInjection\IXarlieMutexExtension;
use IXarlie\MutexBundle\EventListener\MutexDecoratorListener;
use IXarlie\MutexBundle\EventListener\MutexExceptionListener;
use IXarlie\MutexBundle\EventListener\MutexReleaseListener;
use IXarlie\MutexBundle\EventListener\MutexRequestListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Yaml\Yaml;

/**
 * Class IXarlieMutexExtensionTest
 */
class IXarlieMutexExtensionTest extends TestCase
{
    public function testInstance()
    {
        static::assertInstanceOf(ExtensionInterface::class, new IXarlieMutexExtension());
    }

    public function testLoadStoreDefinitions()
    {
        $yaml      = Yaml::parse(file_get_contents(__DIR__ . '/../Fixtures/config/stores.yaml'));
        $container = new ContainerBuilder();
        $extension = new IXarlieMutexExtension();

        // Prepare container with necessary definitions
        $container->setDefinition('my_custom_implementation', new Definition('%app.store.class'));

        $extension->load($yaml, $container);

        $locks   = $yaml['i_xarlie_mutex'];
        $default = $locks['default'];
        unset($locks['default'], $locks['request_listener']);

        $counter = 0;
        foreach ($locks as $type => $definitions) {
            foreach ($definitions as $name => $definition) {
                static::assertTrue($container->hasDefinition(sprintf('ixarlie_mutex.%s_factory.%s', $type, $name)));
                static::assertTrue($container->hasDefinition(sprintf('ixarlie_mutex.%s_store.%s', $type, $name)));
                $counter = $counter + 2;
            }
        }

        list($type, $name) = explode('.', $default);
        static::assertTrue($container->hasAlias(sprintf('ixarlie_mutex.default_factory')));
        static::assertEquals(
            sprintf('ixarlie_mutex.%s_factory.%s', $type, $name),
            $container->getAlias(sprintf('ixarlie_mutex.default_factory'))
        );

        static::assertCount($counter + 2, $container->getDefinitions(), 'Service container plus custom required ones');
        static::assertCount(4, $container->getParameterBag()->all(), 'Symfony StoreInterface classes');
    }

    public function testLoadRequestListenerDefinitions()
    {
        $yaml      = Yaml::parse(file_get_contents(__DIR__ . '/../Fixtures/config/listener.yaml'));
        $container = new ContainerBuilder();
        $extension = new IXarlieMutexExtension();

        // Add required services
        $container->setDefinition('translator', new Definition('%app.translator%'));
        $container->setDefinition('security.token_storage', new Definition('%app.security.token_storage%'));

        $extension->load($yaml, $container);

        $locks    = $yaml['i_xarlie_mutex'];
        $listener = $locks['request_listener'];

        static::assertTrue($listener['enabled']);
        static::assertTrue($container->hasDefinition(MutexRequestListener::class));
        static::assertTrue($container->hasDefinition(MutexDecoratorListener::class));
        static::assertTrue($container->hasDefinition(MutexExceptionListener::class));
        static::assertTrue($container->hasDefinition(MutexReleaseListener::class));

        $definition = $container->getDefinition(MutexRequestListener::class);

        static::assertCount(0, $definition->getMethodCalls(), 'No factory was declared');
        static::assertCount(1, $definition->getTags());
        static::assertCount(1, $definition->getTag('kernel.event_listener'));
        $tags = $definition->getTag('kernel.event_listener');
        static::assertEquals(
            [
                'event'    => KernelEvents::CONTROLLER,
                'method'   => 'onKernelController',
                'priority' => $listener['priority']
            ],
            $tags[0]
        );

        $definition = $container->getDefinition(MutexDecoratorListener::class);

        // Injected using compiler pass
//        static::assertCount(1, $definition->getArguments());
//        static::assertInstanceOf(Reference::class, $definition->getArgument(0));
//        static::assertEquals('security.token_storage', (string) $definition->getArgument(0));

        static::assertCount(0, $definition->getMethodCalls());
        static::assertCount(1, $definition->getTags());
        static::assertCount(1, $definition->getTag('kernel.event_listener'));
        $tags = $definition->getTag('kernel.event_listener');
        static::assertEquals(
            [
                'event'    => KernelEvents::CONTROLLER,
                'method'   => 'onKernelController',
                'priority' => $listener['priority'] + 1
            ],
            $tags[0]
        );

        $definition = $container->getDefinition(MutexExceptionListener::class);

        // Injected using compiler pass
//        static::assertCount(1, $definition->getArguments());
//        static::assertInstanceOf(Reference::class, $definition->getArgument(0));
//        static::assertEquals('translator', (string) $definition->getArgument(0));

        static::assertCount(0, $definition->getMethodCalls());
        static::assertCount(1, $definition->getTags());
        static::assertCount(1, $definition->getTag('kernel.event_listener'));
        $tags = $definition->getTag('kernel.event_listener');
        static::assertEquals(
            [
                'event'    => KernelEvents::EXCEPTION,
                'method'   => 'onKernelException',
                'priority' => 255
            ],
            $tags[0]
        );

        $definition = $container->getDefinition(MutexReleaseListener::class);

        static::assertCount(0, $definition->getArguments());
        static::assertCount(0, $definition->getMethodCalls());
        static::assertCount(1, $definition->getTags());
        static::assertCount(1, $definition->getTag('kernel.event_listener'));
        $tags = $definition->getTag('kernel.event_listener');
        static::assertEquals(
            [
                'event'    => KernelEvents::TERMINATE,
                'method'   => 'onKernelTerminate',
                'priority' => -255
            ],
            $tags[0]
        );

        static::assertCount(4, $container->getParameterBag()->all(), 'Symfony StoreInterface classes');
    }
}
