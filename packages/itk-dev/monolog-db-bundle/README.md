# Monolog db bundle

Write log entries to a database.

Installation:

```sh
composer require itk-dev/monolog-db-bundle "^1.0"
```

Add bundle in your `AppKernel.php`:

```php
    public function registerBundles()
    {
        $bundles = [
            …,
            new ItkDev\MonologDbBundle\ItkDevMonologDbBundle(),
            …
        ];

        …
    }
```

Configuration:

```yml
monolog:
    channels: ['db']
    handlers:
        db:
            channels: ['db']
            type: service
            id: itk_dev.monolog.db_handler
```

Usage:

```php
…
$logger = $container->get('monolog.logger.db');
$logger->info($message);
…
```

Entries logged have a `type` property which can be used for filtering
entries. The type can be set by adding the `type` key to the logging
context:

```php
$logger->info($message, [
  'type' => 'my_log_entry',
]);
```

Command:

```sh
bin/console itk-dev:monolog-db:show --help
```
