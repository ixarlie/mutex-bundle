<?php

namespace IXarlie\MutexBundle\Manager;

use NinjaMutex\Lock\LockExpirationInterface;
use NinjaMutex\Lock\LockInterface;
use NinjaMutex\Mutex;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class LockerManager
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class LockerManager implements LockerManagerInterface
{
    use LoggerAwareTrait;

    /**
     * @var LockInterface
     */
    private $locker;

    /**
     * @var Mutex[]
     */
    private $locks = [];

    /**
     * @param LockInterface        $locker
     * @param LoggerInterface|null $logger
     */
    public function __construct(LockInterface $locker, ?LoggerInterface $logger = null)
    {
        $this->locker = $locker;
        $this->setLogger($logger ?? new NullLogger());
    }

    /**
     * @param string $name
     *
     * @return Mutex
     */
    private function getOrCreateLock(string $name): Mutex
    {
        if (!$this->hasLock($name)) {
            $mutex              = new Mutex($name, $this->locker);
            $this->locks[$name] = $mutex;
        }

        return $this->locks[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function acquireLock(string $name, ?int $timeout = null, ?int $ttl = 0): bool
    {
        $mutex = $this->getOrCreateLock($name);
        if ($this->locker instanceof LockExpirationInterface) {
            $this->locker->setExpiration($ttl);
        }

        return $mutex->acquireLock($timeout);
    }

    /**
     * {@inheritdoc}
     */
    public function releaseLock(string $name): bool
    {
        $mutex = $this->getOrCreateLock($name);

        return $mutex->releaseLock();
    }

    /**
     * {@inheritdoc}
     */
    public function isAcquired(string $name): bool
    {
        $mutex = $this->getOrCreateLock($name);

        return $mutex->isAcquired();
    }

    /**
     * {@inheritdoc}
     */
    public function isLocked(string $name): bool
    {
        $mutex = $this->getOrCreateLock($name);

        return $mutex->isLocked();
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasLock(string $name): bool
    {
        return isset($this->locks[$name]) ? true : false;
    }
}
