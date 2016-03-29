<?php

namespace IXarlie\MutexBundle\Lock;

use NinjaMutex\Lock\LockAbstract;

/**
 * Class RedisLock
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class RedisLock extends LockAbstract
{
    /**
     * @var \Redis
     */
    private $redis;

    /**
     * @param \Redis $redis
     */
    public function __construct(\Redis $redis)
    {
        parent::__construct();

        $this->redis = $redis;
    }

    /**
     * {@inheritdoc}
     */
    protected function getLock($name, $blocking)
    {
        if (!$this->redis->set($name, serialize($this->getLockInformation()))) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function releaseLock($name)
    {
        if (isset($this->locks[$name]) && $this->redis->del($name)) {
            unset($this->locks[$name]);

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
}
