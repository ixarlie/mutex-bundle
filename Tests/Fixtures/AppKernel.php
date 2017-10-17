<?php

namespace IXarlie\MutexBundle\Tests\Fixtures;

use IXarlie\MutexBundle\IXarlieMutexBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

/**
 * Class AppKernel
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class AppKernel extends Kernel
{

    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        return [
            new IXarlieMutexBundle(),
        ];
    }

    /**
     * Loads the container configuration.
     *
     * @param LoaderInterface $loader A LoaderInterface instance
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load($this->getRootDir() . '/config/bundle.yml');
    }

    public function getRootDir()
    {
        return __DIR__;
    }

    public function getCacheDir()
    {
        return $this->getRootDir() . '/cache';
    }

    public function getLogDir()
    {
        return $this->getCacheDir();
    }
}
