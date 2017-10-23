IXarlie Mutex Bundle
===========================

[![Build Status](https://travis-ci.org/ixarlie/mutex-bundle.svg?branch=master)](https://travis-ci.org/ixarlie/mutex-bundle)
[![Maintainability](https://api.codeclimate.com/v1/badges/308f7d2e318ae6ff22e4/maintainability)](https://codeclimate.com/github/ixarlie/mutex-bundle/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/308f7d2e318ae6ff22e4/test_coverage)](https://codeclimate.com/github/ixarlie/mutex-bundle/test_coverage)

Integrates arvenil/ninja-mutex library into Symfony2.

Library repository https://github.com/arvenil/ninja-mutex

## Lockers
* flock (fylesystem)
* redis
* predis
* memcache
* memcached

## Features
* Add services for each registered locker.
* MutexRequest annotation to use mutex in kernel.controller events.


## Install

```sh

composer require ixarlie/mutex-bundle ^0.1

```

Add the bundle to app/AppKernel.php

```php

$bundles(
    ...
       new IXarlie\MutexBundle\IXarlieMutexBundle(),
    ...
);

```

## Configuration

Full configuration options:
```yaml
i_xarlie_mutex:
  # default locker service is mandatory
  default: redis.default
  # configure some aspects of request listener
  request_listener:
    # if you want disable the default listener set this value to false.
    enabled: ~
    # a priority value for the listener, the highest the soonest (not required, default: 255)
    priority: ~
    # true for enable message translation (default: false)
    translator: ~
    # true for be able get a hash for the current token user (default: false)
    user_isolation: ~
    # the max time queue listener will wait for a mutex, as default max_execution_time configuration is taken
    # remember that is max_execution_time = 0, for the queue means it should not have to wait
    queue_timeout: ~
    # the max times queue listener will try for acquiring the mutex (default: 3)
    queue_max_try: ~
    # optional http configuration for default message and code
    http_exception:
      message: 'This is the default block message'
      code: 409
  # you can have several lockers configurations for each type
  flock:
    default:
      cache_dir: '%kernel.cache_dir%'
    other:
      cache_dir: '%temp%'
  memcache:
    default:
      host: '%memcache_host%'
      port: '%memcache_port%'
  memcached:
    default:
      host: '%memcached_host%'
      port: '%memcached_port%'    
  redis:
    default:
      host: '%redis_host%'
      port: '%redis_port%'
  predis:
    default:
      connection:
        host: '%predis_host%'
        port: '%predis_port%'
      options: ~
```

To use your own classes, there are some parameters to do it:
```yaml
parameters:
  i_xarlie_mutex.lock_manager_class: IXarlie\MutexBundle\Model\LockerManager

  ninja_mutex.locker_flock_class: NinjaMutex\Lock\FlockLock
  ninja_mutex.locker_predis_class: NinjaMutex\Lock\PredisRedisLock
  ninja_mutex.locker_memcache_class: NinjaMutex\Lock\MemcacheLock
  ninja_mutex.locker_memcached_class: NinjaMutex\Lock\MemcachedLock
  ninja_mutex.locker_redis_class: IXarlie\MutexBundle\Lock\RedisLock
```

## Annotations

### MutexRequest

`MutexRequest` annotation can be used both ways, as target class or method.

#### Options

##### name

Lock's name. Not required. Default value is a hash combination of controller, method, path (and/or user hash)

Examples:

```php
class MyController extends Controller
{
    /**
     * @Route(name="important_action", path="/resource/{id}/important")
     * @MutexRequest(mode="queue")
     */
    public function importantAction()
    {
        // ...
    }
}
```

```php
/**
 * To block methods each other in the same controller, it's important to use a custom name.
 *
 * @MutexRequest(name="MyController", mode="block")
 */
class MyController extends Controller
{
    public function importantAction()
    {
        // ...
    }
}
```

##### mode

Required option.

| Mode  | Description   |
| ----- | ------------- |
| block | Attempt to acquire the mutex, in case is locked an exception is thrown. |
| check | Check status of the mutex, in case is locked an exception is thrown. (do not attempt to acquire the mutex) |
| queue | Attempt to acquire the mutex, in case is locked, the request wait until the mutex is released. See notes |
| force | Release any locked mutex, then acquire it. |

**Queue Notes**

It is very important when using `queue` option to have well configured the queue options in the `request_listener`.
- `queue_timeout` (default: x): Set a number of seconds the listener will wait for the mutex.
- `queue_max_try` (default: 3): Set the max number of attempts the listener will try to acquire the mutex.

In no value is configured in `queue_timeout` the max_time_execution configuration will be taken, so be aware about this
parameter in your php.ini


##### service

Service to handle the lock. Not required. Default value is locker defined in `default` configuration.

Examples:

With next configuration, below annotations are equivalents.
```yaml
i_xarlie_mutex:
  default: redis.default    
  redis:
    default:
      host: '%redis_host%'
      port: '%redis_port%'
```
```php
class MyController extends Controller
{
    /**
     * @MutexRequest(name="foo", service="i_xarlie_mutex.locker_redis.default")
     * @MutexRequest(name="foo", service="redis.default")
     * @MutexRequest(name="foo", service="i_xarlie_mutex.locker")
     * @MutexRequest(name="foo")
     */
    public function importantAction()
    {
        // ...
    }
}
```

##### httpCode

In `queue`, `block` and `check` modes an `HttpException` can be thrown. Not required. Default value 409.

See configuration option `request_listner.http_exception.code`

##### message

Message for the `HttpException`. Not required. Default value `Resource is not available at this moment.`

See configuration option `request_listner.http_exception.message`

##### messageDomain

Domain to translate the message. Not required.

##### userIsolation

This option allows isolate mutex for each user. Not required. Default value `false`
Note: You must set option request_listener.user_isolation as true
```
i_xarlie_mutex:
  request_listener:
    user_isolation: true
```

Examples:

Two different users requesting same route, a unique hash user will be appended to the name.
Hash is generated from the serialized user's token information.
 
* User1: hash1 -> foo_hash1
* User2: hash2 -> foo_hash2

```php
class MyController extends Controller
{
    /**
     * @MutexRequest(name="foo", userIsolation=true)
     */
    public function importantAction()
    {
        // ...
    }
}
```

Note: Be aware about using `userIsolation` in non anonymous routes.

##### ttl

Time-to-live in seconds. Not required.

Note: Not all lockers are compatible with time-to-live feature. Compatible lockers implements `LockExpirationInterface`
