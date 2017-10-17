<?php

namespace IXarlie\MutexBundle\Tests\Manager;

use IXarlie\MutexBundle\Manager\LockerManager;
use IXarlie\MutexBundle\Manager\LockerManagerInterface;
use IXarlie\MutexBundle\Tests\Fixtures\ArrayLock;

/**
 * Class LockerManagerTest
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class LockerManagerTest extends \PHPUnit_Framework_TestCase
{
    public function testLockerManager()
    {
        $lock    = new ArrayLock();
        $manager = new LockerManager($lock);

        $this->assertInstanceOf(LockerManagerInterface::class, new LockerManager($lock));
        
        $lockName = 'resource';
        
        // Check that there is no lock in the internal array
        $this->assertFalse($manager->hasLock($lockName));
        // Check that really we are not acquiring the lock
        $this->assertFalse($manager->isAcquired($lockName));
        // Check is not locked either
        $this->assertFalse($manager->isLocked($lockName));
        
        $manager->acquireLock($lockName); // lock does not support ttl expiration

        $this->assertTrue($manager->hasLock($lockName));
        $this->assertTrue($manager->isAcquired($lockName));
        $this->assertTrue($manager->isLocked($lockName));

        $manager->releaseLock($lockName);
        
        // Manager still keeps the mutex lock although it was released
        $this->assertTrue($manager->hasLock($lockName));
        // The lock is not acquired after release
        $this->assertFalse($manager->isAcquired($lockName));
        $this->assertFalse($manager->isLocked($lockName));
    }
    
    public function testMultipleAcquire()
    {
        $lock    = new ArrayLock();
        $manager = new LockerManager($lock);
        $this->assertInstanceOf(LockerManagerInterface::class, new LockerManager($lock));

        $lockName = 'resource';

        $this->assertFalse($manager->hasLock($lockName));
        $this->assertFalse($manager->isAcquired($lockName));
        $this->assertFalse($manager->isLocked($lockName));

        // Acquire the mutex several times
        $size = 20;
        foreach (range(1, $size) as $i) {
            $manager->acquireLock($lockName);
        }

        // After lock $size times, the mutex is totally locked
        $this->assertTrue($manager->hasLock($lockName));
        $this->assertTrue($manager->isAcquired($lockName));
        $this->assertTrue($manager->isLocked($lockName));
        
        $counter = 0;
        while ($manager->isLocked($lockName)) {
            $manager->releaseLock($lockName);
            $counter++;
        }
        
        // Effectively mutex is finally release after do it the same times that it was acquired.
        $this->assertEquals($size, $counter);
        $this->assertTrue($manager->hasLock($lockName));
        $this->assertFalse($manager->isAcquired($lockName));
    }
}
