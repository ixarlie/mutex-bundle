# Redis

[RedisStore](https://symfony.com/doc/current/components/lock.html#redisstore)

[Reliability](https://symfony.com/doc/current/components/lock.html#id3)

## Configuration

```yaml
# full configuration
i_xarlie_mutex:
    default: redis.default
    request_listener: ~
    redis:
        default:
            client: 'redis_client'  # a Redis instance service
            default_ttl: 300        # ttl to avoid stalled locks
            blocking:               # decorates with RetryTillSaveStore
                retry_sleep: 900
                retry_count: 3
            logger: 'monolog.logger'
```

## How to create a Redis service

```yaml
# Read Redis documentation to know more options.
services:
    redis_client:
        class: '\Redis'
        calls:
            - ['connect', ['localhost', 6379]]
            - ['auth', 'password']              # optional
            - ['select', 0]                     # optional
```

```yaml
# Read PRedis documentation to know more options.
services:
    predis_client:
        class: '\PRedis\Client'
        arguments:
            - { host: 'localhost', port: 6379 } # connection
            - []                                # options (optional)
```


***
[Back](../README.md)
