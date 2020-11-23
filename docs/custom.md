# Custom

The Custom type let you use a custom `Symfony\Component\Lock\StoreInterface` implementation.

## Configuration

```yaml
# full configuration
i_xarlie_mutex:
    default: custom.default
    request_listener: ~
    custom:
        default:
            service: app.store          # the StoreInterface service
            blocking:                   # decorates with RetryTillSaveStore
                retry_sleep: 900
                retry_count: 3
            logger: 'monolog.logger'
```


***
[Back](../README.md)
