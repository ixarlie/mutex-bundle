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
     * @return bool
     */
    public function acquireLock($name, $timeout = null, $ttl = 0);

    /**
     * @param string $name
     * @return bool
     */
    public function releaseLock($name);

    /**
     * @param string $name
     * @return bool
     */
    public function isAcquired($name);

    /**
     * @param string $name
     * @return bool
     */
    public function isLocked($name);

    /**
     * @param string $name
     * @return bool
     */
    public function hasLock($name);
}
