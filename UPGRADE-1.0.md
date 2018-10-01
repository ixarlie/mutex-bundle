# UPGRADE FROM 0.1 to 1.0

## Important
In this new version, the library was changed completely in favor of `symfony/lock` component. You really have to
consider this in case you want upgrade.

Please, read [Symfony/Lock](https://symfony.com/doc/current/components/lock.html) for learning some concepts.

## Removed locks

* Memcache: consider to use memcached instead.
* Predis: use `redis` and use a predis client as `client`

## Removed classes

* `IXarlie\MutexBundle\Model\LockerManager` was removed in favor of `Symfony\Component\Lock\Factory`
* `IXarlie\MutexBundle\Lock\RedisLock` was removed in favor of `Symfony\Component\Lock\Store\RedisStore`

## Configuration

Changes in the extension configuration:

* Under`flock` the `cache_dir` option was renamed to `lock_dir`
* Under `request_listener` the option `request_placeholder` was removed. The placeholders will be replaced by default
if they are used as part of the lock name.
* Under `request_listener` the option `translator` was removed. The translator will be injected if the definition
exists. If definition is not found, no translation will be done.
* Under `request_listener` the option `user_isolation` was removed. The token storage will be injected if the definition
exists. If `userIsolation` in the annotation is enabled but the definition was not found, an exception will be
thrown.
* Under `request_listener` the option `queue_timeout` was removed. Use the new option `blocking.retry_sleep` when
defining lockers instead.
* Under `request_listener` the option `queue_max_try` was removed. Use the new option `blocking.retry_count` when
defining lockers instead.
* Under `request_listener` the options `http_exception.message` and `http_exception.code` were removed. These values
should be configured in the annotation, the default values for them are kept.
* Under `memcached` the options to configure the client were removed. Use `client` to indicate your own client as a service.
* Under `redis` the options to configure the client were removed. Use `client` to indicate your own client as a service.
* `predis` and `memcache` are not valid types (use `redis` and `memcached` respectively)
* `i_xarlie_mutex.lock_manager_class` parameter was removed. No new parameter is available to change `Symfony\Component\Lock\Factory`
* `ninja_mutex.locker_X_class` parameters were removed. Check new store class parameters.
* The services like `i_xarlie_mutex.locker_X.Y` were changed to `ixarlie_mutex.X_factory.Y`

## Annotation

* The annotation cannot be used for classes anymore.
* The options `httpCode`, `message` and `messageDomain` were moved under the same option `http` with the following names:
`code`, `message` and `domain` respectively.

## LockManager

`Symfony\Component\Lock\Factory` is the new way to create and work with lockers. There are some differences with 
`IXarlie\MutexBundle\Model\LockerManager`.

Method      | Replace with
 ---        | ---         
acquireLock($name, $timeout, $ttl) | acquire($blocking)
releaseLock($name) | release()
isAcquired($name) | isAcquired()
isLocked($name) | -
hasLocked($name) | -

Note: `$name` and `$ttl` are configured when creating the lock from the factory.

Note: `$timeout` is configured in your lock definition in the bundle configuration.
