<?php

namespace IXarlie\MutexBundle\Tests\Manager;

use IXarlie\MutexBundle\Manager\LockerManager;
use IXarlie\MutexBundle\Manager\LockerManagerInterface;
use IXarlie\MutexBundle\Tests\Fixtures\ArrayLock;
use PHPUnit\Framework\TestCase;

/**
 * Class LockerManagerTest
 */
class LockerManagerTest extends TestCase
{
    public function testLockerManager()
    {
        $lock    = new ArrayLock();
        $manager = new LockerManager($lock);

        static::assertInstanceOf(LockerManagerInterface::class, new LockerManager($lock));

        $lockName = 'resource';

        // Check that there is no lock in the internal array
        static::assertFalse($manager->hasLock($lockName));
        // Check that really we are not acquiring the lock
        static::assertFalse($manager->isAcquired($lockName));
        // Check is not locked either
        static::assertFalse($manager->isLocked($lockName));

        $manager->acquireLock($lockName); // lock does not support ttl expiration

        static::assertTrue($manager->hasLock($lockName));
        static::assertTrue($manager->isAcquired($lockName));
        static::assertTrue($manager->isLocked($lockName));

        $manager->releaseLock($lockName);

        // Manager still keeps the mutex lock although it was released
        static::assertTrue($manager->hasLock($lockName));
        // The lock is not acquired after release
        static::assertFalse($manager->isAcquired($lockName));
        static::assertFalse($manager->isLocked($lockName));
    }

    public function testMultipleAcquire()
    {
        $lock    = new ArrayLock();
        $manager = new LockerManager($lock);
        static::assertInstanceOf(LockerManagerInterface::class, new LockerManager($lock));

        $lockName = 'resource';

        static::assertFalse($manager->hasLock($lockName));
        static::assertFalse($manager->isAcquired($lockName));
        static::assertFalse($manager->isLocked($lockName));

        // Acquire the mutex several times
        $size = 20;
        foreach (range(1, $size) as $i) {
            $manager->acquireLock($lockName);
        }

        // After lock $size times, the mutex is totally locked
        static::assertTrue($manager->hasLock($lockName));
        static::assertTrue($manager->isAcquired($lockName));
        static::assertTrue($manager->isLocked($lockName));

        $counter = 0;
        while ($manager->isLocked($lockName)) {
            $manager->releaseLock($lockName);
            $counter++;
        }

        // Effectively mutex is finally release after do it the same times that it was acquired.
        static::assertEquals($size, $counter);
        static::assertTrue($manager->hasLock($lockName));
        static::assertFalse($manager->isAcquired($lockName));
    }
}
