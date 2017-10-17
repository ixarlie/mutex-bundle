<?php

namespace IXarlie\MutexBundle\Lock;

use NinjaMutex\Lock\LockExpirationInterface;
use NinjaMutex\Lock\PhpRedisLock;

/**
 * Class RedisLock
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class RedisLock extends PhpRedisLock implements LockExpirationInterface
{
    /**
     * @var int
     */
    private $expiration = 0;

    /**
     * Stores what ttl was set for a lock
     * @var array
     */
    private $ttl = [];

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
            if (!$this->client->setex($name, $this->expiration, $content)) {
                return false;
            }
            $this->ttl[$name] = $this->expiration;
        } else {
            if (!$this->client->setnx($name, $content)) {
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
        $result = parent::releaseLock($name);
        if ($result) {
            $this->clearLock($name);
        }
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
}
