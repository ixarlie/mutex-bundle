# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2021-10-12
### Added
- `IXarlie\MutexBundle\Exception\MutexException` as a new `HttpException` with `423` http status code
- `IXarlie\MutexBundle\LockingStrategy\LockingStrategy` interface
- `IXarlie\MutexBundle\LockingStrategy\BlockLockingStrategy`
- `IXarlie\MutexBundle\LockingStrategy\CheckLockingStrategy`
- `IXarlie\MutexBundle\LockingStrategy\ForceLockingStrategy`
- `IXarlie\MutexBundle\LockingStrategy\QueueLockingStrategy`
- `IXarlie\MutexBundle\NamingStrategy\NamingStrategy` interface
- `IXarlie\MutexBundle\NamingStrategy\DefaultNamingStrategy`
- `IXarlie\MutexBundle\NamingStrategy\UserIsolationNamingStrategy`
- `IXarlie\MutexBundle\LockExecutor`

### Changed
- `IXarlie\MutexBundle\DependencyInjection\Configuration` new configuration from scratch
- `IXarlie\MutexBundle\Configuration\MutexRequest` moved to `IXarlie\MutexBundle\MutexRequest`
- `IXarlie\MutexBundle\EventListener\MutexRequestListener` split in `IXarlie\MutexBundle\EventListener\ControllerListener`
and `IXarlie\MutexBundle\EventListener\TerminateListener`
- The library `arvenil/ninja-mutex` was abandoned in favor of the `symfony/lock` component 

### Removed
- `IXarlie\MutexBundle\DependencyInjection\Compiler\MutexRequestListenerPass`
- `IXarlie\MutexBundle\DependencyInjection\Definition\FlockDefinition`
- `IXarlie\MutexBundle\DependencyInjection\Definition\LockDefinition`
- `IXarlie\MutexBundle\DependencyInjection\Definition\MemcacheDefinition`
- `IXarlie\MutexBundle\DependencyInjection\Definition\MemcachedDefinition`
- `IXarlie\MutexBundle\DependencyInjection\Definition\PRedisDefinition`
- `IXarlie\MutexBundle\DependencyInjection\Definition\RedisDefinition`
- `IXarlie\MutexBundle\Lock\RedisLock`
- `IXarlie\MutexBundle\Manager\LockManagerInterface`
- `IXarlie\MutexBundle\Manager\LockManager`
- The `check` mode/strategy was removed. Use `block` instead.
