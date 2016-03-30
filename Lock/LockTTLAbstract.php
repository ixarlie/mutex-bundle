<?php

namespace IXarlie\MutexBundle\Lock;

use NinjaMutex\Lock\LockAbstract;

/**
 * Class LockTTLAbstract
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
abstract class LockTTLAbstract extends LockAbstract implements LockTTLInterface
{
    /**
     * Acquire lock
     *
     * @param  string   $name    name of lock
     * @oaram  int      $ttl     time to live
     * @param  null|int $timeout 1. null if you want blocking lock
     *                           2. 0 if you want just lock and go
     *                           3. $timeout > 0 if you want to wait for lock some time (in milliseconds)
     * @return bool
     */
    public function acquireLockTTL($name, $ttl, $timeout = null)
    {
        $blocking = $timeout === null;
        $start = microtime(true);
        $end = $start + $timeout / 1000;
        $locked = false;
        while (!(empty($this->locks[$name]) && $locked = $this->getLockTTL($name, $ttl, $blocking)) &&
            ($blocking || ($timeout > 0 && microtime(true) < $end))
        ) {
            usleep(static::USLEEP_TIME);
        }

        if ($locked) {
            $this->locks[$name] = true;

            return true;
        }

        return false;
    }

    /**
     * @param  string $name
     * @param  int    $ttl
     * @param  bool   $blocking
     * @return bool
     */
    abstract protected function getLockTTL($name, $ttl, $blocking);
}
