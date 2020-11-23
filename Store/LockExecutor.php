<?php

namespace IXarlie\MutexBundle\Store;

use IXarlie\MutexBundle\Configuration\MutexRequest;
use IXarlie\MutexBundle\Exception\MutexException;
use Symfony\Component\Lock\Exception\LockAcquiringException;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\Exception\LockExpiredException;
use Symfony\Component\Lock\Exception\LockReleasingException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

/**
 * Class LockExecutor.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class LockExecutor
{
    /**
     * @var LockFactory
     */
    private $factory;

    /**
     * @var MutexRequest
     */
    private $configuration;

    /**
     * LockExecutor constructor.
     *
     * @param LockFactory  $factory
     * @param MutexRequest $configuration
     */
    public function __construct(LockFactory $factory, MutexRequest $configuration)
    {
        $this->factory       = $factory;
        $this->configuration = $configuration;
    }

    /**
     * @return LockInterface
     * @throws MutexException
     */
    public function execute(): LockInterface
    {
        $lock = $this->factory->createLock($this->configuration->getName(), $this->configuration->getTtl());

        try {
            $this->executeMode($lock, $this->configuration->getMode());
        } catch (LockConflictedException|LockAcquiringException|LockReleasingException|LockExpiredException $e) {
            throw new MutexException($lock, $this->configuration, $e);
        }

        return $lock;
    }

    /**
     * @param LockInterface $lock
     * @param string        $mode
     */
    private function executeMode(LockInterface $lock, string $mode)
    {
        switch ($mode) {
            case MutexRequest::MODE_CHECK:
                $this->check($lock);
                break;
            case MutexRequest::MODE_BLOCK:
                $this->block($lock);
                break;
            case MutexRequest::MODE_QUEUE:
                $this->queue($lock);
                break;
            case MutexRequest::MODE_FORCE:
                $this->force($lock);
                break;
        }
    }

    /**
     * Check if the lock is locked or not.
     *
     * @param LockInterface $lock
     *
     * @throws LockAcquiringException
     */
    private function check(LockInterface $lock)
    {
        if (false === $lock->isAcquired()) {
            return;
        }

        throw new LockAcquiringException(sprintf('Lock "%s" is already acquired', $this->configuration->getName()));
    }

    /**
     * Attempt to acquire the lock.
     *
     * @param LockInterface $lock
     */
    private function block(LockInterface $lock)
    {
        $this->check($lock);
        $lock->acquire(false);
    }

    /**
     * @param LockInterface $lock
     */
    private function queue(LockInterface $lock)
    {
        $lock->acquire(true);
    }

    /**
     * @param LockInterface $lock
     */
    private function force(LockInterface $lock)
    {
        if ($lock->isAcquired()) {
            $lock->release();
        }

        $lock->acquire(false);
    }
}
