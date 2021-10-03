<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\Tests\LockingStrategy;

use IXarlie\MutexBundle\LockingStrategy\LockingStrategy;
use IXarlie\MutexBundle\LockingStrategy\QueueLockingStrategy;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Lock\LockInterface;

/**
 * Class QueueLockingStrategyTest.
 */
final class QueueLockingStrategyTest extends TestCase
{
    public function testInstance(): void
    {
        self::assertInstanceOf(LockingStrategy::class, new QueueLockingStrategy());
    }

    public function testGetName(): void
    {
        $strategy = new QueueLockingStrategy();

        self::assertSame('queue', $strategy->getName());
    }

    public function testExecuteIsAcquired(): void
    {
        $strategy = new QueueLockingStrategy();
        $lock     = $this->createMock(LockInterface::class);

        $lock
            ->expects(self::once())
            ->method('acquire')
            ->with(true)
        ;

        $strategy->execute($lock);
    }

    public function testExecuteIsNotAcquired(): void
    {
        $strategy = new QueueLockingStrategy();
        $lock     = $this->createMock(LockInterface::class);

        $lock
            ->expects(self::once())
            ->method('acquire')
            ->with(true)
        ;

        $strategy->execute($lock);
    }
}
