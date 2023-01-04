This document details the changes that you need to make to your code when upgrading from one version to another.

Upgrading from 1.x to 2.0
=========================

:warning: The lock definitions are now provided by the `symfony/lock` component. You really have to consider this if you
want to upgrade. :warning:

Please, read [Symfony/Lock](https://symfony.com/doc/current/components/lock.html)


- Configuration

    All the previous configuration is no longer valid. You should convert your _lockers_ definitions to an equivalent
 [Symfony Lock](https://symfony.com/doc/current/components/lock.html#available-stores).


- Namespace Changes

    You can adjust your projects by performing these replacements (in order):

    - `IXarlie\MutexBundle\Configuration\MutexRequest` -> `IXarlie\MutexBundle\MutexRequest`


- Renamed classes

    - `IXarlie\MutexBundle\EventListener\MutexRequestListener` in favor of
      `IXarlie\MutexBundle\EventListener\ControllerListener` and `IXarlie\MutexBundle\EventListener\TerminateListener`


- Removed classes

    The following classes were removed with no replacement.

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


- Annotation changes

    - The annotation cannot target classes anymore. Add the necessary annotations on every method instead.
    - The `name` option no longer supports request attribute placeholders.
    - The `service` option is now mandatory. Use any registered Symfony lock factory service.
    - The `mode` option was renamed to `strategy`. Remains as mandatory.
    - The `check` mode was removed. Use `block` instead.
    - The `httpCode` option was removed. The default status code is `423`. Use the `kernel.exception` event to decorate
      the response.
    - The `messageDomain` option was removed. Use the `kernel.exception` event to decorate the response.
