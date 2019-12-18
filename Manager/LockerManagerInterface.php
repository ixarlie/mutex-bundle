<?php

namespace IXarlie\MutexBundle\Manager;

/**
 * Interface LockerManagerInterface
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
interface LockerManagerInterface
{
    /**
     * @param string   $name
     * @param int|null $timeout
     * @param int      $ttl
     *
     * @return bool
     */
    public function acquireLock(string $name, ?int $timeout = null, ?int $ttl = 0): bool;

    /**
     * @param string $name
     *
     * @return bool
     */
    public function releaseLock(string $name): bool;

    /**
     * @param string $name
     *
     * @return bool
     */
    public function isAcquired(string $name): bool;

    /**
     * @param string $name
     *
     * @return bool
     */
    public function isLocked(string $name): bool;

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasLock(string $name): bool;
}
