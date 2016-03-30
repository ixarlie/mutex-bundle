<?php

namespace IXarlie\MutexBundle\Lock;

use NinjaMutex\Lock\LockInterface;

/**
 * Interface LockTTLInterface
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
interface LockTTLInterface extends LockInterface
{
    /**
     * @param  string   $name
     * @param  int      $ttl
     * @param  null|int $timeout
     * @return bool
     */
    public function acquireLockTTL($name, $ttl, $timeout = null);
}
