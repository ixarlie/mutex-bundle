<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\LockingStrategy;

use Symfony\Component\Lock\Exception\LockAcquiringException;
use Symfony\Component\Lock\LockInterface;

/**
 * Class BlockLockingStrategy.
 * It attempts to acquire the lock. If the lock is already acquired then an exception is thrown.
 * This strategy does not block until the release of the lock.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 * @final
 */
class BlockLockingStrategy implements LockingStrategy
{
    /**
     * @inheritDoc
     */
    public function execute(LockInterface $lock): void
    {
        if (false === $lock->acquire(false)) {
            throw new LockAcquiringException('Lock is already acquired.');
        }
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'block';
    }
}
