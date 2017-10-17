IXarlie Mutex Bundle
===========================

[![Build Status](https://travis-ci.org/ixarlie/mutex-bundle.svg?branch=master)](https://travis-ci.org/ixarlie/mutex-bundle)

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
    # specify a default locker service is mandatory
    default: redis.default
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
            host: '%predis_host%'
            port: '%predis_port%'
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
    
    i_xarlie_mutex.http_exception.message: 'Resource is not available at this moment.'
    i_xarlie_mutex.http_exception.code: 409
```

## Annotations

### MutexRequest

`MutexRequest` annotation can be used both ways, as target class or method.

#### Options

##### name

Lock's name. Not required. Default value is the requested relative path (i.e.: _resource_1_update)
Note: slashes are replaced with underscores.

Examples:

```php
// name is not in the options, so default value is MyController_importantAction__resource_1_important
// (1 is the example value for {id} placeholder)
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
| queue | Attempt to acquire the mutex, in case is locked, the request wait until the mutex is released. |
| force | Release any locked mutex, then acquire it. |

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

In `block` and `check` modes an `HttpException` can be thrown. Not required. Default value 409.

See parameter `i_xarlie_mutex.http_exception.code`

##### message

Message for the `HttpException`. Not required. Default value `Resource is not available at this moment.`

See parameter `i_xarlie_mutex.http_exception.message`

##### messageDomain

Domain to translate the message. Not required.

##### userIsolation

This option allows isolate mutex for each user. Not required. Default value `false`

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
