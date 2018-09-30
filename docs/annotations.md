# MutexRequest

`MutexRequest` annotation can be used both ways, as target class or method.

#### Options

##### name

Lock's name. Not required. Default value is a hash combination of controller, method, path (and/or user hash)

You can use request placeholders. For example: resource_{id}, {id} will be
replaced for the request placeholder value. If there is no placeholder in the request and exception is thrown. 

Examples:

```php
class MyController extends Controller
{
    /**
     * @Route(name="important_action", path="/resource/{id}/important")
     * @MutexRequest(mode="queue")
     */
    public function importantAction()
    {
        // ...
    }
}
```

```php
/**
 * To block methods each other in the same controller, it's important to use a custom name.
 *
 * @MutexRequest(name="MyController", mode="block")
 */
class MyController extends Controller
{
    public function importantAction()
    {
        // ...
    }
}
```

```php
class MyController extends Controller
{
    /**
     * @MutexRequest(name="resource_{id}")
     * @Request(name="resource_edit", path="/resource/{id}/edit")
     */ 
    public function importantAction()
    {
        // ...
    }
}
```

##### mode

Required option.

| Mode  | Description   |
| ----- | ------------- |
| block | Attempt to acquire the mutex, in case is locked an exception is thrown. |
| check | Check status of the mutex, in case is locked an exception is thrown. (do not attempt to acquire the mutex) |
| queue | Attempt to acquire the mutex, in case is locked, the request wait until the mutex is released. See notes |
| force | Release any locked mutex, then acquire it. |

**Queue Notes**

It is very important when using `queue` option to have well configured the queue options in the `request_listener`.
- `queue_timeout` (default: x): Set a number of seconds the listener will wait for the mutex.
- `queue_max_try` (default: 3): Set the max number of attempts the listener will try to acquire the mutex.

In no value is configured in `queue_timeout` the max_time_execution configuration will be taken, so be aware about this
parameter in your php.ini


##### service

Service to handle the lock. Not required. Default value is locker defined in `default` configuration.

Examples:

With next configuration, below annotations are equivalents.
```yaml
i_xarlie_mutex:
  default: redis.default    
  redis:
    default:
      host: '%redis_host%'
      port: '%redis_port%'
```
```php
class MyController extends Controller
{
    /**
     * @MutexRequest(name="foo", service="ixarlie_mutex.redis_factory.default")
     * @MutexRequest(name="foo", service="redis.default")
     * @MutexRequest(name="foo")
     */
    public function importantAction()
    {
        // ...
    }
}
```

##### httpCode

In `queue`, `block` and `check` modes an `HttpException` can be thrown. Not required. Default value 409.

See configuration option `request_listner.http_exception.code`

##### message

Message for the `HttpException`. Not required. Default value `Resource is not available at this moment.`

See configuration option `request_listner.http_exception.message`

##### messageDomain

Domain to translate the message. Not required.

##### userIsolation

This option allows isolate mutex for each user. Not required. Default value `false`

Examples:

Two different users requesting same route, a unique hash user will be appended to the name.
Hash is generated from the serialized user's token information.
 
* User1: hash1 -> foo_hash1
* User2: hash2 -> foo_hash2

```php
class MyController extends Controller
{
    /**
     * @MutexRequest(name="foo", userIsolation=true)
     */
    public function importantAction()
    {
        // ...
    }
}
```

Note: Be aware about using `userIsolation` in non anonymous routes.

##### ttl

Time-to-live in seconds. Not required.

Note: Not all lockers are compatible with time-to-live feature. Compatible lockers implements `LockExpirationInterface`
