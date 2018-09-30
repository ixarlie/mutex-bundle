#Flock

[RedisStore](https://symfony.com/doc/current/components/lock.html#flockstore)
[Reliability](https://symfony.com/doc/current/components/lock.html#id1)

## Configuration

```yaml
# full configuration
i_xarlie_mutex:
    default: flock.default
    request_listener: ~
    flock:
        default:
            lock_dir: '/tmp/flock'
            blocking:                   # decorates with RetryTillSaveStore
                retry_sleep: 900
                retry_count: 3
            logger: 'monolog.logger'
```

***
[Back](../README.md)
