<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\LockingStrategy;

use Symfony\Component\Lock\LockInterface;

/**
 * Class QueueLockStrategyInterface.
 * It attempts to acquire the lock. Whether the lock is acquired, this strategy will wait until the release of the lock.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 * @final
 */
class QueueLockingStrategy implements LockingStrategy
{
    /**
     * @inheritDoc
     */
    public function execute(LockInterface $lock): void
    {
        $lock->acquire(true);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'queue';
    }
}
