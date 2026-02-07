This document details the changes that you need to make to your code when upgrading from one version to another.

Upgrading from 2.x to 3.0
=========================

Locking Strategy
----------------

- The `LockingStrategy` interface dropped the `getName` method. Use the `alias` property in the `ixarlie_mutex.strategy`
  tag.

Before:

```php
class MyLock implements LockingStrategy {

    public function execute(LockInterface $lock): void
    {
        // ...
    }

    public function getName(): string
    {
        return 'my_lock';
    }
}
```
```yaml
services:
    app.mutex_locking_strategy:
        class: App\Mutex\MyLock
        tags:
            - { name: ixarlie_mutex.strategy }
```

After:

```php
class MyLock implements LockingStrategy {

    public function execute(LockInterface $lock): void
    {
        // ...
    }
}
```
```yaml
services:
    app.mutex_locking_strategy:
        class: App\Mutex\MyLock
        tags:
            - { name: ixarlie_mutex.strategy, alias: 'my_lock' }
```
