# IXarlie Mutex Bundle

[![GitHub Actions][GA Image]][GA Link]
[![Codacy Badge](https://app.codacy.com/project/badge/Grade/c867ebceca884f43ae1fdb4b2f087573)](https://www.codacy.com/gh/ixarlie/mutex-bundle/dashboard?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=ixarlie/mutex-bundle&amp;utm_campaign=Badge_Grade)
[![Packagist][Packagist Image]][Packagist Link]

This bundle integrates the `symfony/lock` capabilities into `kernel.controller` events.

For previous releases with `arvenil/ninja-mutex` dependency follow the [version 1](https://github.com/ixarlie/mutex-bundle/tree/v1.0.4)


Before continuing, please read the following links for more information.
- [Symfony/Lock](https://symfony.com/doc/current/components/lock.html)
- [Concurrency with Locks](https://symfony.com/doc/current/lock.html)


## Install

```sh
composer require ixarlie/mutex-bundle "^2.0"
```

Add the bundle in the kernel class:

```php
$bundles = [
    // ...
    IXarlie\MutexBundle\IXarlieMutexBundle::class => ['all' => true],
    // ...
];
```


## Configuration
```yaml
# Symfony lock configuration
framework:
    lock:
        main: flock
        alt: semaphore 
```

```yaml
i_xarlie_mutex:
    # Add the Symfony lock factories services id
    factories:
        - 'lock.default.factory'
        - 'lock.main.factory'
        - 'lock.alt.factory' 
```


## Locking Strategy

This bundle ships 3 different locking strategies:

- `block` (`BlockLockingStrategy`)

    It attempts to acquire the lock. If the lock is already acquired then an exception is thrown. This strategy does
    not block until the release of the lock.

- `force` (`ForceLockingStrategy`)

    It acquires the lock. Whether the lock is acquired, it forces a release before acquire it.

- `queue` (`QueueLockingStrategy`)

    It attempts to acquire the lock. Whether the lock is acquired, this strategy will wait until the release of the lock.
    The `queue` strategy will work depending on the `service` configuration.

    Read `Blocking` section in [Symfony Docs](https://symfony.com/doc/current/components/lock.html#blocking-locks)


You can implement your own `LockingStrategy` classes. Use the tag `ixarlie_mutex.strategy` in your services to register
them in the `LockExecutor` service.

```yaml
services:
    app.mutex_locking_strategy:
        class: App\Mutex\LockingStrategy
        tags:
            - { name: ixarlie_mutex.strategy }
```


## Naming Strategy

The `name` option is not required in the annotation. However, a name is mandatory in order to create a `LockInterface`
instance.

This bundle ships 2 naming strategies:

- `DefaultNamingStrategy`, if a `name` is not set in the annotation, this class will use the request information.
- `UserIsolationNamingStrategy`, if the `userIsolation` is enabled, this class will append the token user information
to the `name` value. It decorates `DefaultNamingStrategy`.

You can implement your own `NamingStrategy`.

1. Decorating `ixarlie_mutex.naming_strategy` (recommended)
```yaml
services:
    app.mutex_naming_strategy:
        class: App\Mutex\NamingStrategy
        decorates: 'ixarlie_mutex.naming_strategy'
        arguments: ['app.mutex_naming_strategy.inner']
```

2. Replacing the alias definition `ixarlie_mutex.naming_strategy` with your own service id. This will execute only your
 logic.
```yaml
services:
    app.mutex_naming_strategy:
        class: App\Mutex\NamingStrategy

    ixarlie_mutex.naming_strategy:
        alias: app.mutex_naming_strategy
```


## Annotation

The `MutexRequest` annotation can be used only on controller methods.

### Options

- `service` (required)

The lock factory service name. It should be one of the services listed in the `factories` setting.

Examples:
```yaml
framework:
    lock: semaphore

i_xarlie_mutex:
    factories:
        - 'lock.default.factory'
```
```
@MutexRequest(service="lock.default.factory")
```

```yaml
framework:
    lock:
        main_lock: flock
        secondary_lock: semaphore

i_xarlie_mutex:
    factories:
        - 'lock.main_lock.factory'
```
```
@MutexRequest(service="lock.main_lock.factory")
```

- `strategy` (required)

One of the registered locking strategies. Read the `Locking Strategy` section.

Examples:
```
@MutexRequest(service="lock.default.factory", strategy="block")
@MutexRequest(service="lock.default.factory", strategy="queue")
@MutexRequest(service="lock.default.factory", strategy="force") 
```

- `name` (optional)

The lock's name. If no name is provided, the name will be generated using the registered naming strategies.


Note: Read `userIsolation` option to know how it affects to the name.

Note: The prefix `ixarlie_mutex_` is prepended to every locker.

Note: The naming strategy output is md5 hashed to avoid any issue with some storage implementations.

Examples:
```
@MutexRequest(service="lock.default.factory", strategy="block")
@MutexRequest(service="lock.default.factory", strategy="block", name="lock_name")
```

- `message` (optional)

This is a custom message for the exception in case the lock cannot be acquired.

Examples:
```
@MutexRequest(service="lock.default.factory", strategy="block", message="Busy!")
```

- `userIsolation` (optional, default: false)

This option will add token user context to the `name` option in order to have isolated locks for different users.

Example:
```
@MutexRequest(service="lock.default.factory", strategy="block", userIsolation=true)
```

Note: If `security.token_storage` is not available and `userIsolation` is set to true, an exception will be thrown.

Note: Be aware about using `userIsolation` in anonymous routes.

- `ttl` (optional)

Maximum expected lock duration in seconds.

Example:
```php
class MyController {

    /**
     * @MutexRequest(
     *     service="lock.default.factory,
     *     strategy="block",
     *     name="action_name",
     *     userIsolation=true,
     *     message="Busy!"
     *     ttl=20.0 
     * )
     */
    public function foo()
    {
        return [];
    }
}
```

[GA Image]: https://github.com/ixarlie/mutex-bundle/workflows/CI/badge.svg
[GA Link]: https://github.com/ixarlie/mutex-bundle/actions?query=workflow%3A%22CI%22+branch%3Amaster
[Packagist Image]: https://img.shields.io/packagist/v/ixarlie/mutex-bundle.svg
[Packagist Link]: https://packagist.org/packages/ixarlie/mutex-bundle
