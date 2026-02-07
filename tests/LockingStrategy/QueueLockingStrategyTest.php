<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\Tests\LockingStrategy;

use IXarlie\MutexBundle\LockingStrategy\QueueLockingStrategy;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Lock\LockInterface;

#[CoversClass(QueueLockingStrategy::class)]
final class QueueLockingStrategyTest extends TestCase
{
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
