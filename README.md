#IXarlie Mutex Bundle

[![Build Status](https://travis-ci.org/ixarlie/mutex-bundle.svg?branch=master)](https://travis-ci.org/ixarlie/mutex-bundle)
[![Maintainability](https://api.codeclimate.com/v1/badges/308f7d2e318ae6ff22e4/maintainability)](https://codeclimate.com/github/ixarlie/mutex-bundle/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/308f7d2e318ae6ff22e4/test_coverage)](https://codeclimate.com/github/ixarlie/mutex-bundle/test_coverage)

Integrates symfony/lock component to register locks as services.


## Types
* [Flock](docs/flock.md)
* [Semaphore](docs/semaphore.md)
* [Redis](docs/redis.md)
* [Memcached](docs/memcached.md)
* [Combined](docs/combined.md)
* [Custom](docs/custom.md)


## Features
* MutexRequest annotation to use mutex in `kernel.controller` event.


## Install

```sh
composer require ixarlie/mutex-bundle "^1.0"
```

Add the bundle in the kernel class:

```php
// prior Symfony 4
$bundles = array(
    // ...
    new IXarlie\MutexBundle\IXarlieMutexBundle(),
    // ...
);

// Symfony 4
$bundles = [
    // ...
    IXarlie\MutexBundle\IXarlieMutexBundle::class => ['all' => true],
    // ...
];
```


## Configuration

Any number of lockers can be defined with their own custom options.

See [Full configuration](docs/full_configuration.md) section for further information.

```yaml
i_xarlie_mutex:
    default: flock.default
    request_listener:
        enabled: true
    flock:
        default:
            lock_dir: '%kernel.cache_dir%'
            logger: monolog.logger
```

Some services will be created using this configuration.

- `ixarlie_mutex.flock_factory.default`, allow creates lockers
- `ixarlie_mutex.flock_store.default`, it is the store instance. It is private but you can use it as dependency.
- `ixarlie_mutex.default_factory`, as the default option matches type.name = flock.default, it points to `ixarlie_mutex.flock_factory.default`


To use your own store implementations, just replace these parameters:
```yaml
parameters:
    ixarlie_mutex.flock_store.class: Symfony\Component\Lock\Store\FlockStore
    ixarlie_mutex.semaphore_store.class: Symfony\Component\Lock\Store\SemaphoreStore
    ixarlie_mutex.memcached_store.class: Symfony\Component\Lock\Store\MemcachedStore
    ixarlie_mutex.redis_store.class: Symfony\Component\Lock\Store\RedisStore
```


## Event Listener

To use this option the configuration `request_listener.enabled` should be set to `true`.

It allows to add lockers in your controllers using an annotation. The annotation does have several options changing the
way the locker is executed.

The purpose for this is avoid concurrent requests for the same resource.

The listener priority is high (255 by default), this bundle have to boost `Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener`
priority to read annotations easier.

See [Annotations](docs/annotations.md) section for further information.
