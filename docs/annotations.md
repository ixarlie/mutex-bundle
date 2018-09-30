# Annotations

`MutexRequest` annotation can be used only on controller methods.

## Options

### name
`not required`

Lock's name. If no name is provided, the name will be created using request information and other configuration options.

The name could contains request placeholders. For example: `resource_{id}`

The placeholder `{id}` will be replaced with the request `_route_params` value. If there is no placeholder an exception
is thrown.

Note: Read `userIsolation` option to know how it affects to the name.
Note: The prefix `ixarlie_mutex_` is prepend to every locker.
Note: The name uses a md5 hash (to avoid issue with some stores)

Examples:
```
@MutexRequest(mode="block")
@MutexRequest(name="resource_{id}", mode="block")
```

### mode
`required`
Required option.

| Mode  | Description   |
| ----- | ------------- |
| block | Attempt to acquire the mutex, in case is locked an exception is thrown. |
| check | Check status of the mutex, in case is locked an exception is thrown. (do not attempt to acquire the mutex) |
| queue | Attempt to acquire the mutex, in case is locked, the request wait until the mutex is released. See notes |
| force | Release any locked mutex, then acquire it. |

Examples:
```
@MutexRequest(mode="block")
@MutexRequest(mode="check")
@MutexRequest(mode="queue")
@MutexRequest(mode="force") 
```

**Queue Notes**

The `queue` mode will work depending on the `service` configuration and by the store's features.

Read `Blocking` section in [Symfony Docs](https://symfony.com/doc/current/components/lock.html#blocking-locks)

Not all the stores implements this feature. For example: `RedisStore` does not support it and it should use `blocking`
option in its configuration. You can defined `retry_sleep` and `retry_count` options.

Built-in supported stores: `flock`, `semaphore`, `custom` (depending on your custom implementation)

```yaml
i_xarlie_mutex:
    default: redis.default
    redis:
        default:                    # default cannot use acquire(true)
            client: redis_client
        queued:                     # queued can use acquire(true)
            client: redis_client
            blocking:
                retry_sleep: 1000 # waits for the lock
                retry_count: 3    # number of attempts
```

Examples:
```
@MutexRequest(mode="queue", service="redis.queued")

// This is not going to work and an exception will be thrown.
@MutexRequest(mode="queue", service="redis.default") 
```

### service
`not required`

The factory service name. If not value is provided the default value will be taken from the `default` configuration.

If you want to use an specific factory it is recommended to use the simple form `{factory_type}.{factory_name}`.
Also you can use the full name form `ixarlie_mutex.{factory_type}_factory.{factory_name}`

Examples:

With next configuration, below annotations are equivalents.
```yaml
i_xarlie_mutex:
    default: redis.default    
    redis:
        default:
            client: redis_client
            logger: monolog.logger
```
```
@MutexRequest(name="foo", service="ixarlie_mutex.redis_factory.default")
@MutexRequest(name="foo", service="redis.default")
@MutexRequest(name="foo")
```

### http
`not required`

When the lock is acquired some exception can be thrown. The array of available options are:

- code: HTTP code status. Default: `423`
- message: HTTP message. Default: `Resource is not available at this moment.`
- domain: A translation domain to enable the message translation. Default: `~`

Note: `domain` will use the Symfony's translator service (if exists) to translate `message`.
Note: Modes `queue`, `block` and `check` can throw `MutexException`.

### userIsolation
`not required``default = false`

This option allows isolate lockers for each user. An unique hash user will be append to the lock's name. The user information
is taken from the `security.token_storage` Symfony service.

Example:
```
@MutexRequest(name="foo", userIsolation=true)
```

Note: If `security.token_storage` is not defined and `userIsolation` is used, an expcetion will be thrown.
Note: Be aware about using `userIsolation` in non anonymous routes.

### ttl
`not required`

Maximum expected lock duration in seconds.

Note: Compatible stores: `redis`, `memcached`, `combined` (depending of store list)


## Listener

### Configuration

```yaml
i_xarlie_mutex:
    request_listener:
        enabled: true       # this option enable all the request feature
        priority: 255       # the listeners should be executed as soon as possible
        autorelease: true   # release all the collected lockers on terminate
```

There are four built-in listeners:

Note: `{priority}` can be configured in `request_listener.priority`

### MutexDecoratorListener
`kernel.controller` `priority: {priority + 1}`

After `Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener` is executed, this listener will process the
annotation configuration and it will set the default options for `name` and `service`.

### MutexRequestListener
`kernel.controller` `priority: {prioriry}`

After `MutexDecoratorListener`, this listener will create the lockers and will execute it depending on the `mode` option.

### MutexExceptionListener
`kernel.exception` `priority: 255`

If a `MutexException` exception is thrown, this listener will catch it, and it will replace it with an `HttpException`.

The `HttpException` is configured depending on the `http` option. The new exception will be processed for the regular
listeners.

### MutexReleaseListener
`kernel.terminate` `priority: -255`

Despite lockers are created using the Symfony option `autorelease`, this listener will release all the collected lockers.

Note: The bundle boost `Sensio\Bundle\FrameworkExtraBundle\EventListener\ControllerListener` priority for easy annotation
reading.


***
[Back](../README.md)
