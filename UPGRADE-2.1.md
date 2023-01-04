This document details the changes that you need to make to your code when upgrading from one version to another.

Upgrading from 2.0 to 2.1
=========================

- PHP Attributes

  The annotation based on Doctrine is no longer supported and it was replaced with a native PHP attribute.

Before:

```php
/**
 * @MutexRequest(service="lock.default.factory", strategy="block", userIsolation=true)
 */
```

After:

```php
#[MutexRequest(service: 'lock.default.factory', strategy: 'block', userIsolation: true)]
```
