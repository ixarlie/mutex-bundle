# Configuration

```yaml
# Symfony lock configuration
framework:
    lock:
        main: flock
        alt: semaphore
```

```yaml
i_xarlie_mutex:
    # Add the Symfony lock factories services id
    factories:
        - 'lock.default.factory'
        - 'lock.main.factory'
        - 'lock.alt.factory'
```
