# Combined

[CombinedStore](https://symfony.com/doc/current/components/lock.html#combinedstore)
[Reliability](https://symfony.com/doc/current/components/lock.html#id4)

## Configuration

```yaml
# full configuration
i_xarlie_mutex:
    default: combined.default
    request_listener: ~
    combined:
        default:
            stores: [flock.other, redis.other, ixarlie_mutex.memcached_store.default]
            blocking:                   # decorates with RetryTillSaveStore
                retry_sleep: 900
                retry_count: 3
            logger: 'monolog.logger'
```


***
[Back](../README.md)
