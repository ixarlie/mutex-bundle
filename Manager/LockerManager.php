<?php

namespace IXarlie\MutexBundle\Manager;

use NinjaMutex\Lock\LockExpirationInterface;

/**
 * Class LockerManager
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class LockerManager implements LockerManagerInterface
{
    /**
     * @var \NinjaMutex\Lock\LockInterface
     */
    private $locker;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \NinjaMutex\Mutex[]
     */
    private $locks;

    /**
     * @param \NinjaMutex\Lock\LockInterface $locker
     */
    public function __construct(\NinjaMutex\Lock\LockInterface $locker, \Psr\Log\LoggerInterface $logger = null)
    {
        $this->locker = $locker;
        $this->logger = $logger;
        $this->locks  = [];
    }

    /**
     * @param string $name
     * @return \NinjaMutex\Mutex
     */
    private function getOrCreateLock($name)
    {
        if (!$this->hasLock($name)) {
            $mutex = new \NinjaMutex\Mutex($name, $this->locker);
            $this->locks[$name] = $mutex;
        }
        return $this->locks[$name];
    }

    /**
     * {@inheritdoc}
     */
    public function acquireLock($name, $timeout = null, $ttl = 0)
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
    public function releaseLock($name)
    {
        $mutex = $this->getOrCreateLock($name);
        return $mutex->releaseLock();
    }

    /**
     * {@inheritdoc}
     */
    public function isAcquired($name)
    {
        $mutex = $this->getOrCreateLock($name);
        return $mutex->isAcquired();
    }

    /**
     * {@inheritdoc}
     */
    public function isLocked($name)
    {
        $mutex = $this->getOrCreateLock($name);
        return $mutex->isLocked();
    }

    /**
     * @param string $name
     * @return bool
     */
    public function hasLock($name)
    {
        return isset($this->locks[$name]) ? true : false;
    }
}
