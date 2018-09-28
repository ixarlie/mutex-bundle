<?php

namespace IXarlie\MutexBundle\Tests\Fixtures;

use Symfony\Component\Lock\Exception\LockAcquiringException;
use Symfony\Component\Lock\Exception\LockConflictedException;
use Symfony\Component\Lock\Exception\LockReleasingException;
use Symfony\Component\Lock\LockInterface;

/**
 * Class ArrayLock
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class ArrayLock implements LockInterface
{
    /**
     * @param  string $name
     * @param  bool $blocking
     * @return bool
     */
    protected function getLock($name, $blocking)
    {
        $content = serialize($this->getLockInformation());
        if (isset($this->locks[$name])) {
            return false;
        }

        $this->locks[$name] = $content;

        return true;
    }

    /**
     * @param  string $name
     * @return bool
     */
    public function releaseLock($name)
    {
        unset($this->locks[$name]);
        return true;
    }

    /**
     * @param  string $name
     * @return bool
     */
    public function isLocked($name)
    {
        return isset($this->locks[$name]);
    }

    /**
     * Acquires the lock. If the lock is acquired by someone else, the parameter `blocking` determines whether or not
     * the call should block until the release of the lock.
     *
     * @param bool $blocking Whether or not the Lock should wait for the release of someone else
     *
     * @return bool whether or not the lock had been acquired
     *
     * @throws LockConflictedException If the lock is acquired by someone else in blocking mode
     * @throws LockAcquiringException  If the lock can not be acquired
     */
    public function acquire($blocking = false)
    {
        // TODO: Implement acquire() method.
    }

    /**
     * Increase the duration of an acquired lock.
     *
     * @param float|null $ttl Maximum expected lock duration in seconds
     *
     * @throws LockConflictedException If the lock is acquired by someone else
     * @throws LockAcquiringException  If the lock can not be refreshed
     */
    public function refresh(/* $ttl = null */)
    {
        // TODO: Implement refresh() method.
    }

    /**
     * Returns whether or not the lock is acquired.
     *
     * @return bool
     */
    public function isAcquired()
    {
        // TODO: Implement isAcquired() method.
    }

    /**
     * Release the lock.
     *
     * @throws LockReleasingException If the lock can not be released
     */
    public function release()
    {
        // TODO: Implement release() method.
    }

    /**
     * @return bool
     */
    public function isExpired()
    {
        // TODO: Implement isExpired() method.
    }

    /**
     * Returns the remaining lifetime.
     *
     * @return float|null Remaining lifetime in seconds. Null when the lock won't expire.
     */
    public function getRemainingLifetime()
    {
        // TODO: Implement getRemainingLifetime() method.
    }
}
