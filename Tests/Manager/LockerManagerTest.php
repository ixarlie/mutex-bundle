<?php

namespace IXarlie\MutexBundle\Tests\Manager;

use IXarlie\MutexBundle\Manager\LockerManager;
use IXarlie\MutexBundle\Manager\LockerManagerInterface;
use NinjaMutex\Lock\FlockLock;

/**
 * Class LockerManagerTest
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 */
class LockerManagerTest extends \PHPUnit_Framework_TestCase
{
    const TMP_DIR = __DIR__ . '/../Fixtures/tmp';

    public function testLockerManager()
    {
        // Use flock locker to test the manager because is the easiest implementation.
        $flock   = new FlockLock(self::TMP_DIR);
        $manager = new LockerManager($flock);

        $this->assertInstanceOf(LockerManagerInterface::class, new LockerManager($flock));
        
        $lockName = 'resource';
        
        // Check that there is no lock in the internal array
        $this->assertFalse($manager->hasLock($lockName));
        // Check that really we are not acquiring the lock
        $this->assertFalse($manager->isAcquired($lockName));
        // Check is not locked either
        $this->assertFalse($manager->isLocked($lockName));
        
        $manager->acquireLock($lockName); // flock does not support ttl expiration

        $this->assertTrue($manager->hasLock($lockName));
        $this->assertTrue($manager->isAcquired($lockName));
        $this->assertTrue($manager->isLocked($lockName));

        // As this is a flock we could check that the file exists
        $this->assertFileExists(self::TMP_DIR.'/'.$lockName.'.lock');
        
        // Release lock does not removed the flock, just use flock php function
        $manager->releaseLock($lockName);
        
        // Manager still keeps the mutex lock although it was released
        $this->assertTrue($manager->hasLock($lockName));
        // The lock is not acquired after release
        $this->assertFalse($manager->isAcquired($lockName));
        $this->assertFalse($manager->isLocked($lockName));
        
        // destroy lock file
        unlink(self::TMP_DIR.'/'.$lockName.'.lock');
    }
    
    public function testMultipleAcquire()
    {
        // Use flock locker to test the manager because is the easiest implementation.
        $flock   = new FlockLock(self::TMP_DIR);
        $manager = new LockerManager($flock);
        $this->assertInstanceOf(LockerManagerInterface::class, new LockerManager($flock));

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
        $this->assertFileExists(self::TMP_DIR.'/'.$lockName.'.lock');
        
        $counter = 0;
        while ($manager->isLocked($lockName)) {
            $manager->releaseLock($lockName);
            $counter++;
        }
        
        // Effectively mutex is finally release after do it the same times that it was acquired.
        $this->assertEquals($size, $counter);
        $this->assertTrue($manager->hasLock($lockName));
        $this->assertFalse($manager->isAcquired($lockName));

        // destroy lock file
        unlink(self::TMP_DIR.'/'.$lockName.'.lock');
    }
}
