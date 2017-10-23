<?php

namespace IXarlie\MutexBundle\Tests\Util;

use IXarlie\MutexBundle\DependencyInjection\Configuration;
use IXarlie\MutexBundle\Manager\LockerManager;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\DependencyInjection\Compiler\Compiler;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\DependencyInjection\MergeExtensionConfigurationPass;

trait UtilTestTrait
{
    /**
     * @param array $params
     *
     * @return ContainerBuilder
     */
    private function getContainer(array $params = [])
    {
        $params = array_replace($params, [
            'kernel.debug'          => false,
            'kernel.bundles'        => [],
            'kernel.cache_dir'      => sys_get_temp_dir(),
            'kernel.environment'    => 'test',
            'kernel.root_dir'       => __DIR__ . '/../../' // src dir
        ]);
        return new ContainerBuilder(new ParameterBag($params));
    }

    /**
     * @return Definition
     */
    private function getServiceDefinition()
    {
        return new Definition(LockerManager::class);
    }

    /**
     * @param string $locker
     * @param array  $config
     * @param string $lockerName
     *
     * @return array
     */
    private function processConfiguration($locker, array $config = [], $lockerName = 'default')
    {
        $processor     = new Processor();
        $configuration = new Configuration();

        $config = [
            'default' => sprintf('%s_%s', $locker, $lockerName),
            $locker => [$lockerName => $config]
        ];
        $normalized = $processor->processConfiguration($configuration, [$config]);
        
        return $normalized[$locker][$lockerName];
    }

    /**
     * Prepares the ContainerBuilder before it is compiled.
     *
     * @param ContainerBuilder $container
     * @param BundleInterface  $bundle
     * @param array            $config
     */
    protected function prepareContainer(ContainerBuilder $container, BundleInterface $bundle, array $config)
    {
        $extensions    = [];
        if ($extension = $bundle->getContainerExtension()) {
            $container->registerExtension($extension);
            $extensions[] = $extension->getAlias();
        }
        $container->loadFromExtension('i_xarlie_mutex', $config);
        $bundle->build($container);

        // ensure these extensions are implicitly loaded
        $container->getCompilerPassConfig()->setMergePass(new MergeExtensionConfigurationPass($extensions));
        $container->compile();

        $bundle->setContainer($container);
        $bundle->boot();
    }
}
