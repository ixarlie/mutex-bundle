<?php

namespace IXarlie\MutexBundle\Lock;

use NinjaMutex\Mutex;

/**
 * Class MutexTTL
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class MutexTTL extends Mutex
{
    /**
     * @var LockTTLInterface
     */
    protected $lockImplementor;

    public function __construct($name, LockTTLInterface $lockImplementor)
    {
        parent::__construct($name, $lockImplementor);
    }

    /**
     * @param  int      $ttl
     * @param  int|null $timeout
     * @return bool
     */
    public function acquireLockTTL($ttl, $timeout = null)
    {
        if ($this->counter > 0 ||
            $this->lockImplementor->acquireLockTTL($this->name, $ttl, $timeout)) {
            $this->counter++;

            return true;
        }

        return false;
    }


}
