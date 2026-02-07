<?php declare(strict_types=1);

namespace IXarlie\MutexBundle;

use IXarlie\MutexBundle\Exception\MutexException;
use IXarlie\MutexBundle\LockingStrategy\LockingStrategy;
use Symfony\Component\Lock\Exception\ExceptionInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

/**
 * @author Carlos Dominguez <ixarlie@gmail.com>
 *
 * @final
 */
class LockExecutor
{
    /**
     * @var array<string, LockFactory>
     */
    private array $factories = [];

    /**
     * @var array<string, LockingStrategy>
     */
    private array $strategies;

    /**
     * @param iterable<LockingStrategy> $strategies
     */
    public function __construct(iterable $strategies)
    {
        $this->strategies = $strategies instanceof \Traversable ? iterator_to_array($strategies) : $strategies;
    }

    public function addLockFactory(string $id, LockFactory $factory): void
    {
        $this->factories[$id] = $factory;
    }

    /**
     * @throws MutexException
     */
    public function execute(MutexRequest $config): LockInterface
    {
        if (null === $config->name) {
            throw new \RuntimeException('Configuration must have a name.');
        }

        $factory = $this->factories[$config->service] ?? null;
        if (null === $factory) {
            throw new \RuntimeException(sprintf('Cannot find the "%s" service.', $config->service));
        }

        $strategy = $this->strategies[$config->strategy] ?? null;
        if (null === $strategy) {
            throw new \RuntimeException(sprintf('Cannot find the "%s" strategy.', $config->strategy));
        }

        try {
            $lock = $factory->createLock($config->name, $config->ttl);
            $strategy->execute($lock);
        } catch (ExceptionInterface $e) {
            throw new MutexException($config, $e);
        }

        return $lock;
    }
}
