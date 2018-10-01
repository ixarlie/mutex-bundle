# Full configuration

```yaml
i_xarlie_mutex:
    # default locker service is mandatory (type.name)
    default: redis.default
    # configure some aspects of request listener
    request_listener:
        # if you want disable the default listener set this value to false.
        enabled: true
        # a priority value for the listener, the highest the soonest (not required, default: 255)
        priority: 255
        # add a kernel.terminate listener to release all the collected lockers
        autorelease: true
    # you can have several lockers configurations for each type
    # blocking option is present for every type but "combined".
    # logger is present for every type, it is a logger service name.
    flock:
        default:
            lock_dir: '/tmp/flock'    # a writable directory
            blocking:
                retry_sleep: 500
                retry_count: 3
            logger: monolog.logger
        other:
            lock_dir: '/tmp/flock'
    semaphore:
        default:
    memcached:
        default:
            client: client_name
            default_ttl: 300        # ttl to avoid stalled locks
            blocking:
                retry_sleep: 500
                retry_count: 3
            logger: monolog.logger
    redis:
        default:
            client: client_name
            default_ttl: 300        # ttl to avoid stalled locks
            blocking:
                retry_sleep: 500
                retry_count: 3
            logger: monolog.logger
    combined:
        default:
            stores: [redis.default, ixarlie_mutex.memcached_store.default]
            strategy: unanimous # unanimous, consensus or a StrategyInterface service name
            blocking:                   # decorates with RetryTillSaveStore
                retry_sleep: 900
                retry_count: 3
            logger: 'monolog.logger'
    custom:
        default:
            service: app.store_lock   # StoreInterface service
            blocking:                   # decorates with RetryTillSaveStore
                retry_sleep: 900
                retry_count: 3
            logger: monolog.logger
```

***
[Back](../README.md)
