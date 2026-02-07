# IXarlie Mutex Bundle

This bundle integrates the `symfony/lock` capabilities into `kernel.controller` events.

[![GitHub Actions][GA Image]][GA Link]
[![Packagist][Packagist Image]][Packagist Link]

## Prerequisites

The IXarlieMutexBundle has the following requirements:
- PHP 8.1+
- Symfony 6.4 or Symfony 7.4

## Installation

### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the following command to download the latest stable
version of this bundle:

```console
composer require ixarlie/mutex-bundle
```

This command requires you to have Composer installed globally, as explained in
the [installation chapter](https://getcomposer.org/doc/00-intro.md) of the Composer documentation.

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    IXarlie\MutexBundle\IXarlieMutexBundle::class => ['all' => true],
];
```

### Step 3: Configure the Bundle

```yaml
# config/packages/i_xarlie_mutex.yml
i_xarlie_mutex:
    # Add the Symfony lock factories services id
    factories:
        - 'lock.default.factory'
```

## Documentation

- [Examples](docs/index.md)
- [Configuration](docs/configuration.md)

## License

This bundle is under the MIT license. See the complete license [in the bundle](LICENSE).

[GA Image]: https://github.com/ixarlie/mutex-bundle/workflows/CI/badge.svg
[GA Link]: https://github.com/ixarlie/mutex-bundle/actions?query=workflow%3A%22CI%22+branch%3Amaster
[Packagist Image]: https://img.shields.io/packagist/v/ixarlie/mutex-bundle.svg
[Packagist Link]: https://packagist.org/packages/ixarlie/mutex-bundle
