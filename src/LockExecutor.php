<?php declare(strict_types=1);

namespace IXarlie\MutexBundle;

use IXarlie\MutexBundle\Exception\MutexException;
use IXarlie\MutexBundle\LockingStrategy\LockingStrategy;
use Symfony\Component\Lock\Exception\ExceptionInterface;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;

/**
 * Class LockExecutor.
 *
 * @author Carlos Dominguez <ixarlie@gmail.com>
 * @final
 */
class LockExecutor
{
    /**
     * @var LockFactory[]
     */
    private $factories = [];

    /**
     * @var LockingStrategy[]
     */
    private $strategies = [];

    /**
     * @param string      $id
     * @param LockFactory $factory
     */
    public function addLockFactory(string $id, LockFactory $factory): void
    {
        $this->factories[$id] = $factory;
    }

    /**
     * @param LockingStrategy $strategy
     */
    public function addLockStrategy(LockingStrategy $strategy): void
    {
        $name = $strategy->getName();
        if (array_key_exists($name, $this->strategies)) {
            throw new \RuntimeException('Cannot register the same strategy more than once.');
        }

        $this->strategies[$name] = $strategy;
    }

    /**
     * @param MutexRequest $config
     *
     * @return LockInterface
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
