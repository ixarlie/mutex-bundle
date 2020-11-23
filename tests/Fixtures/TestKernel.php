<?php

namespace Tests\Fixtures;

use IXarlie\MutexBundle\IXarlieMutexBundle;
use Sensio\Bundle\FrameworkExtraBundle\SensioFrameworkExtraBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

/**
 * Class TestKernel.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
final class TestKernel extends Kernel
{
    use MicroKernelTrait;

    /**
     * @inheritdoc
     */
    public function registerBundles()
    {
        yield new FrameworkBundle();
        yield new SensioFrameworkExtraBundle();
        yield new IXarlieMutexBundle();
    }

    /**
     * @inheritdoc
     */
    public function getCacheDir()
    {
        return __DIR__ . '/../var/cache/' . $this->environment;
    }

    /**
     * @inheritdoc
     */
    public function getLogDir()
    {
        return __DIR__ . '/../var/log/' . $this->environment;
    }

    /**
     * @inheritdoc
     */
    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $routes->add('/test-block', 'Tests\Fixtures\DemoController::blockAction', 'test_block');
        $routes->add('/test-queue', 'Tests\Fixtures\DemoController::queueAction', 'test_queue');
        $routes->add('/test-force', 'Tests\Fixtures\DemoController::forceAction', 'test_force');
        $routes->add('/test-check', 'Tests\Fixtures\DemoController::checkAction', 'test_check');
    }

    /**
     * @inheritdoc
     */
    protected function configureContainer(ContainerBuilder $c, LoaderInterface $loader)
    {
        $c->register('translator', FakeTranslator::class);
        $c->register('security.token_storage', FakeTokenStorage::class);

        $loader->load(__DIR__ . '/config/bundle.yaml');
    }
}
