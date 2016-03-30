<?php

namespace IXarlie\MutexBundle\Lock;

use NinjaMutex\Lock\LockAbstract;
use NinjaMutex\Lock\LockExpirationInterface;
use NinjaMutex\UnrecoverableMutexException;

/**
 * Class RedisLock
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class RedisLock extends LockAbstract implements LockExpirationInterface
{
    /**
     * @var \Redis
     */
    private $redis;

    /**
     * @var int
     */
    private $expiration = 0;

    /**
     * Stores what ttl was set for a lock
     * @var array
     */
    private $ttl;

    /**
     * @param \Redis $redis
     */
    public function __construct(\Redis $redis)
    {
        parent::__construct();

        $this->redis = $redis;
        $this->ttl   = [];
    }

    /**
     * {@inheritdoc}
     */
    public function setExpiration($expiration = 0)
    {
        if ($expiration < 0) {
            $expiration = 0;
        }
        $this->expiration = $expiration;
    }

    /**
     * {@inheritdoc}
     */
    protected function getLock($name, $blocking)
    {
        $content = serialize($this->getLockInformation());
        if ($this->expiration > 0) {
            if (!$this->redis->setex($name, $this->expiration, $content)) {
                return false;
            }
            $this->ttl[$name] = $this->expiration;
        } else {
            if (!$this->redis->setnx($name, $content)) {
                return false;
            }
            unset($this->ttl[$name]);
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function releaseLock($name)
    {
        if (isset($this->locks[$name]) && $this->redis->del($name)) {
            $this->clearLock($name);

            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function isLocked($name)
    {
        return false !== $this->redis->get($name);
    }

    /**
     * {@inheritdoc}
     */
    public function clearLock($name)
    {
        if (!isset($this->locks[$name])) {
            return false;
        }

        unset($this->locks[$name]);
        unset($this->ttl[$name]);
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function __destruct()
    {
        foreach ($this->locks as $name => $v) {
            // Redis will remove the lock when time expires, clearing lock
            if (isset($this->ttl[$name])) {
                $this->clearLock($name);
            } else {
                $released = $this->releaseLock($name);
                if (!$released) {
                    throw new UnrecoverableMutexException(sprintf(
                        'Cannot release lock in __destruct(): %s',
                        $name
                    ));
                }
            }
        }
    }
}
