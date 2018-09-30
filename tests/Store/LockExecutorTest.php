<?php

namespace Tests\Store;

use IXarlie\MutexBundle\Configuration\MutexRequest;
use IXarlie\MutexBundle\Store\LockExecutor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Lock\Store\FlockStore;
use Tests\Fixtures\ArrayStore;

/**
 * Class LockExecutorTest.
 */
class LockExecutorTest extends TestCase
{
    public function testBlock()
    {
        $store         = new FlockStore();
        $factory       = new Factory($store);
        $configuration = new MutexRequest([
           'mode' => MutexRequest::MODE_BLOCK,
           'name' => 'lock_name',
        ]);

        $executor = new LockExecutor($factory, $configuration);
        $lock     = $executor->execute();

        static::assertInstanceOf(LockInterface::class, $lock);
        static::assertTrue($lock->isAcquired());
    }

    public function testQueue()
    {
        $store         = new ArrayStore();
        $factory       = new Factory($store);
        $configuration = new MutexRequest([
            'mode' => MutexRequest::MODE_QUEUE,
            'name' => 'lock_name',
        ]);

        $executor = new LockExecutor($factory, $configuration);
        $lock     = $executor->execute();

        static::assertInstanceOf(LockInterface::class, $lock);
        static::assertTrue($lock->isAcquired());
    }

    public function testCheck()
    {
        $store         = new ArrayStore();
        $factory       = new Factory($store);
        $configuration = new MutexRequest([
            'mode' => MutexRequest::MODE_CHECK,
            'name' => 'lock_name',
        ]);

        $executor = new LockExecutor($factory, $configuration);
        $lock     = $executor->execute();

        static::assertInstanceOf(LockInterface::class, $lock);
        static::assertFalse($lock->isAcquired());
    }

    public function testForce()
    {
        $store         = new ArrayStore();
        $factory       = new Factory($store);
        $configuration = new MutexRequest([
            'mode' => MutexRequest::MODE_FORCE,
            'name' => 'lock_name',
        ]);

        $executor = new LockExecutor($factory, $configuration);
        $lock     = $executor->execute();

        static::assertInstanceOf(LockInterface::class, $lock);
        static::assertTrue($lock->isAcquired());
    }
}
