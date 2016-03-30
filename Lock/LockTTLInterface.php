<?php

namespace IXarlie\MutexBundle\Lock;

/**
 * Interface LockTTLInterface
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
interface LockTTLInterface
{
    /**
     * @param  string   $name
     * @param  int      $ttl
     * @param  null|int $timeout
     * @return bool
     */
    public function acquireLockTTL($name, $ttl, $timeout = null);
}
