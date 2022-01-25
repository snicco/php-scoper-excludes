# A simple CLI tool to generate exclusion rules for PHP-Scoper.

You put in any php file and get a several files that contain a list of fully-namespaced:

- class names
- interface names
- trait names
- function names
- constant names

## Example:

Let's assume that you have the following stub file:

````php
namespace WP_CLI
{
    
    trait FooTrait
    {
    
    }
    
    class Autoloader
    {
        
        public function foo()
        {
        }
        
    }
    
    function foo_func()
    {
    }
    
    define('FOO', 'BAR');
    
    const BAZ = 'BIZ';
}

namespace WP_CLI\Utils
{
    
    function wp_not_installed()
    {
    }
    
    const BAM = 'boom';
}

namespace WP_CLI\Bootstrap
{
    
    interface BootstrapInterface
    {
    
    }
    
    trait BarTrait
    {
    
    }
    
    abstract class AutoloaderStep
    {
    
    }
}
````

The generated exclusion lists would be:

```php
// exclude-wp-cli-classes.php
<?php return array (
  0 => 'WP_CLI\\Autoloader',
  1 => 'WP_CLI\\Bootstrap\\AutoloaderStep',
);
```

```php
// exclude-wp-cli-functions.php
<?php return array (
  0 => 'WP_CLI\\foo_func',
  1 => 'WP_CLI\\Utils\\wp_not_installed',
);
```

```php
// exclude-wp-cli-constants.php
<?php return array (
  0 => 'FOO',
  1 => 'WP_CLI\\BAZ',
  2 => 'WP_CLI\\Utils\\BAM',
);
```

```php
// exclude-wp-cli-interfaces.php
<?php return array (
  0 => 'WP_CLI\\Bootstrap\\BootstrapInterface',
);
```

```php
// exclude-wp-cli-interfaces.php
<?php return array (
  0 => 'WP_CLI\\FooTrait',
  1 => 'WP_CLI\\Bootstrap\\BarTrait',
);

```

After generating your necessary files you can use them in combination with
[excluded-symbols feature](https://github.com/humbug/php-scoper#excluded-symbols).

We already generate exclusion lists for WordPress, WP-CLI and WooCommerce. You can find them here:

- [sniccowp/php-scoper-wordpress-excludes](https://github.com/sniccowp/php-scoper-wordpress-excludes)
- [sniccowp/php-scoper-wp-cli-excludes](https://github.com/sniccowp/php-scoper-wp-cli-excludes)
- [sniccowp/php-scoper-woocommerce-excludes](https://github.com/sniccowp/php-scoper-woocommerce-excludes)

These are automatically generated using the published stubs from: https://github.com/php-stubs

It's not a requirement to use stubs files. Any php code should work.

## Using

### Installation

```shell
composer/require sniccowp/php-scoper-excludes --dev
```

### Create a configuration

Generates a configuration file in the current working directory:

```shell
vendor/bin/generate-excludes generate-config
```

```php
// generate-excludes.inc.php
return [
    Option::EMULATE_PHP_VERSION => Option::PHP_8_0,
    Option::OUTPUT_DIR => __DIR__.'/excludes',
    Option::FILES => [
        Finder::create()->files()
              ->in(__DIR__.'/vendor/php-stubs')
              ->depth('< 3')
              ->name('*.php'),
    ],
];
```

Using `symfony/finder` is totally optional. You can also provide a list of strings or any iterable.

### Generating your exclusion lists

```shell
vendor/bin/generate-excludes
```

### Credits:

None of this would be possible without [nikic/php-parser.](https://github.com/nikic/PHP-Parser)