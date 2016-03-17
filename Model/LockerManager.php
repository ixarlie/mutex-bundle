<?php

namespace IXarlie\MutexBundle\Model;

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
     * {@inheritdoc}
     */
    public function acquireLock($name, $timeout = null)
    {
        if ($this->hasLock($name)) {
            throw new \LogicException(sprintf('%s mutex is already registered', $name));
        }
        $mutex = new \NinjaMutex\Mutex($name, $this->locker);
        $this->locks[$name] = $mutex;

        return $mutex->acquireLock($timeout);
    }

    /**
     * {@inheritdoc}
     */
    public function releaseLock($name)
    {
        if (!$this->hasLock($name)) {
            throw new \LogicException(sprintf('%s mutex is not registered', $name));
        }
        $lock = $this->locks[$name];
        if ($lock->releaseLock()) {
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isAcquired($name)
    {
        if (!$this->hasLock($name)) {
            throw new \LogicException(sprintf('%s mutex is not registered', $name));
        }
        $lock = $this->locks[$name];
        return $lock->isAcquired();
    }

    /**
     * {@inheritdoc}
     */
    public function isLocked($name)
    {
        if (!$this->hasLock($name)) {
            throw new \LogicException(sprintf('%s mutex is not registered', $name));
        }
        $lock = $this->locks[$name];
        return $lock->isLocked();
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
