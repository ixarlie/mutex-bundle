<?php

namespace IXarlie\MutexBundle\Tests\Lock;

use IXarlie\MutexBundle\Lock\RedisLock;
use IXarlie\MutexBundle\Tests\Fixtures\RedisDouble;
use NinjaMutex\Lock\LockAbstract;
use NinjaMutex\Lock\LockExpirationInterface;

/**
 * Class RedisLockTest
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class RedisLockTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Build a RedisLock without a real \Redis instance
     *
     * @return RedisLock
     */
    private function getRedisMock()
    {
        $refClass = new \ReflectionClass(RedisLock::class);
        $instance = $refClass->newInstanceWithoutConstructor();
        
        $refProp  = $refClass->getProperty('client');
        $refProp->setAccessible(true);
        $refProp->setValue($instance, new RedisDouble());
        
        // lock information
        $refMethod = $refClass->getMethod('generateLockInformation');
        $refMethod->setAccessible(true);
        $value = $refMethod->invoke($instance);
        $refProp = $refClass->getProperty('lockInformation');
        $refProp->setAccessible(true);
        $refProp->setValue($instance, $value);

        return $instance;
    }

    /**
     * @param RedisLock $lock
     *
     * @return RedisDouble
     */
    private function getRedisClient(RedisLock $lock)
    {
        $refClass = new \ReflectionClass(RedisLock::class);
        $refProp  = $refClass->getProperty('client');
        $refProp->setAccessible(true);
        return $refProp->getValue($lock);
    }
    
    public function testInstance()
    {
        $lock = $this->getRedisMock();
        $this->assertInstanceOf(LockAbstract::class, $lock);
        $this->assertInstanceOf(LockExpirationInterface::class, $lock);
    }
    
    public function testLock()
    {
        $lock = $this->getRedisMock();
        $name = 'resource';
        
        $this->assertFalse($lock->isLocked($name));
        $this->assertTrue($lock->acquireLock($name));
        $this->assertTrue($lock->isLocked($name));
        
        $this->assertTrue($lock->releaseLock($name));
        $this->assertFalse($lock->isLocked($name));
    }
    
    public function testMultipleAcquire()
    {
        $lock = $this->getRedisMock();
        $name = 'resource';

        $this->assertFalse($lock->isLocked($name));
        $this->assertTrue($lock->acquireLock($name));
        $this->assertTrue($lock->isLocked($name));
        $this->assertFalse($lock->acquireLock($name, 2000)); // wait 2 seconds, after that the acquire fails

        $this->assertTrue($lock->releaseLock($name));
        $this->assertFalse($lock->isLocked($name));
    }
    
    public function testClearLock()
    {
        $lock = $this->getRedisMock();
        $name = 'resource';

        $this->assertFalse($lock->isLocked($name));
        $this->assertTrue($lock->acquireLock($name));
        $this->assertTrue($lock->isLocked($name));
        
        // This does not release the mutex and it should be used unless you know what you do
        $lock->clearLock($name);
        $this->assertTrue($lock->isLocked($name));

        // As the locker was cleared, then releaseLock does not have any effect.
        $this->assertFalse($lock->releaseLock($name));
        $this->assertTrue($lock->isLocked($name));
        
        // In a real scenario, Redis will keep the lock infinitely, so you should be very careful.
        // In tests RedisDouble will be destroy when finishing this
    }
    
    public function testExpiration()
    {
        $lock  = $this->getRedisMock();
        $redis = $this->getRedisClient($lock);
        $name  = 'resource';
        
        // Expiration in 5 seconds
        $lock->setExpiration(5);

        $this->assertFalse($lock->isLocked($name));
        $this->assertTrue($lock->acquireLock($name));
        $this->assertTrue($lock->isLocked($name));
        
        // Check expiration time. As we are not using real redis we have to simulate this behaviour in a loop
        while ($redis->ttl($name) > 0) {
            $redis->refreshExpiration($lock, $name);
        }

        $this->assertFalse($lock->isLocked($name));
    }
}
