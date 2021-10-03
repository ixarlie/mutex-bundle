<?php declare(strict_types=1);

namespace IXarlie\MutexBundle\LockingStrategy;

use Symfony\Component\Lock\LockInterface;

/**
 * Class BlockLockingStrategy.
 * It attempts to acquire the lock. If the lock is already acquired then an exception is thrown.
 * This strategy does not block until the release of the lock.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 * @final
 */
class BlockLockingStrategy extends CheckLockingStrategy
{
    /**
     * @inheritDoc
     */
    public function execute(LockInterface $lock): void
    {
        parent::execute($lock);
        $lock->acquire(false);
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return 'block';
    }
}
