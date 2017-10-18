<?php

namespace IXarlie\MutexBundle\Tests\Fixtures;

use IXarlie\MutexBundle\Lock\RedisLock;

/**
 * Class RedisDouble
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class RedisDouble
{
    private $cache      = [];
    private $expiration = [];
    
    public function setex($name, $ttl, $content)
    {
        $this->setnx($name, $content);
        // ttl, start time, remaining
        $this->expiration[$name] = [$ttl, time(), $ttl];
        
        return true;
    }
    
    public function setnx($name, $content)
    {
        $this->cache[$name] = $content;
        
        return true;
    }
    
    public function get($name)
    {
        return isset($this->cache[$name]) ? $this->cache[$name] : false;
    }
    
    public function del($name)
    {
        unset($this->cache[$name]);
        
        return 1;
    }

    public function ttl($name)
    {
        if (!isset($this->cache[$name])) {
            return -2; // no key
        }
        if (isset($this->expiration[$name])) {
            return $this->expiration[$name][2];
        } else {
            return -1; // no expiration
        }
    }

    /**
     * This method simulates a "real" expiration count down
     * @param RedisLock $lock
     * @param string    $name
     */
    public function refreshExpiration(RedisLock $lock, $name = null)
    {
        if (null !== $name && isset($this->expiration[$name])) {
            $expiration = [$name => $this->expiration[$name]];
        } else {
            $expiration = $this->expiration;
        }
        foreach ($expiration as $name => list($ttl, $start, $remain)) {
            $current = time();
            $remain  = $remain - ($current - $start);
            if ($remain < 1) {
                $lock->releaseLock($name);
                unset($this->cache[$name]);
                unset($this->expiration[$name]);
            } else {
                $this->expiration[$name] = [$ttl, $current, $remain];    
            }
        }
    }
}
