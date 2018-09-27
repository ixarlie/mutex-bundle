<?php

namespace IXarlie\MutexBundle\Store;

use IXarlie\MutexBundle\Configuration\MutexRequest;
use IXarlie\MutexBundle\Exception\MutexException;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\LockInterface;

/**
 * Class LockExecutor.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class LockExecutor
{
    /**
     * @var Factory
     */
    private $factory;

    /**
     * @var MutexRequest
     */
    private $configuration;

    /**
     * LockExecutor constructor.
     * @param Factory      $factory
     * @param MutexRequest $configuration
     */
    public function __construct(Factory $factory, MutexRequest $configuration)
    {
        $this->factory       = $factory;
        $this->configuration = $configuration;
    }

    /**
     * @return LockInterface
     * @throws MutexException
     */
    public function execute()
    {
        $lock = $this->factory->createLock(
            $this->configuration->getName(),
            $this->configuration->getTtl()
        );

        switch ($this->configuration->getMode()) {
            case MutexRequest::MODE_CHECK:
                $this->check($lock, $this->configuration);
                break;
            case MutexRequest::MODE_BLOCK:
                $this->block($lock, $this->configuration);
                break;
            case MutexRequest::MODE_QUEUE:
                $this->queue($lock, $this->configuration);
                break;
            case MutexRequest::MODE_FORCE:
                $this->force($lock, $this->configuration);
                break;
        }

        return $lock;
    }

    /**
     * Check if the lock is locked or not.
     *
     * @param LockInterface $lock
     * @param MutexRequest  $configuration
     *
     * @throws MutexException
     */
    private function check(LockInterface $lock, MutexRequest $configuration)
    {
        if (false === $lock->isAcquired()) {
            return;
        }

        throw new MutexException($lock, $configuration);
    }

    /**
     * Attempt to acquire the lock.
     *
     * @param LockInterface $lock
     * @param MutexRequest  $configuration
     *
     * @throws MutexException
     */
    private function block(LockInterface $lock, MutexRequest $configuration)
    {
        $this->check($lock, $configuration);
        $lock->acquire(true);
    }

    /**
     * @param LockInterface $lock
     * @param MutexRequest  $configuration
     */
    private function queue(LockInterface $lock, MutexRequest $configuration)
    {
        $lock->acquire(true);
    }

    /**
     * @param LockInterface $lock
     * @param MutexRequest  $configuration
     */
    private function force(LockInterface $lock, MutexRequest $configuration)
    {
        if ($lock->isAcquired()) {
            $lock->release();
        }

        $lock->acquire(true);
    }
}
