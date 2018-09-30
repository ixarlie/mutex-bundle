#Semaphore

[RedisStore](https://symfony.com/doc/current/components/lock.html#semaphorestore)
[Reliability](https://symfony.com/doc/current/components/lock.html#id5)

## Configuration

```yaml
# full configuration
i_xarlie_mutex:
    default: semaphore.default
    request_listener: ~
    semaphore:
        default:
            logger: 'monolog.logger'
```

***
[Back](../README.md)
