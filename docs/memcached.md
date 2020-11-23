# Memcached

[MemcachedStore](https://symfony.com/doc/current/components/lock.html#memcachedstore)

[Reliability](https://symfony.com/doc/current/components/lock.html#id2)

## Configuration

```yaml
# full configuration
i_xarlie_mutex:
    default: redis.default
    request_listener: ~
    memcached:
        default:
            client: 'memcached_client'  # a Redis instance service
            default_ttl: 300            # ttl to avoid stalled locks
            blocking:                   # decorates with RetryTillSaveStore
                retry_sleep: 900
                retry_count: 3
            logger: 'monolog.logger'
```

## How to create a Memcached service

```yaml
# Read Memcached documentation to know more options.
services:
    memcached_client:
        class: '\Memcached'
        calls:
            - ['addServer', ['localhost', 1000]] 
```


***
[Back](../README.md)
