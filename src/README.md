### Config and command has been based on ziggy to provide familiar interface

## How to use
* Controllers must use a custom request for validation
* Rules from the validation request will be parsed into types
* Parsed rules are then used to generate a typescript interface
* Use typescript interface freely

```bash
php artisan export:requests
```

## Configuring
A config can be specified by making `config/request-types.php`

### Filtering routes
See https://github.com/tighten/ziggy#filtering-routes

### Resolving custom types
To extend existing type resolving functionality, you can specify a custom type resolver class in config
```php
// config/request-types.php

return [
    'resolver' => MyCustomResolver::class
]
```
Your custom resolver should implement `TypesResolverInterface`
```php
// MyCustomResolver.php

class MyCustomResolver implements TypesResolverInterface {
    // ...
}
```

### Altering output
Output can be altered similarly to Ziggy
```php
// config/request-types.php

return [
    'output' => [
        'file' => MyCustomOutput::class,
    ],
]
```
Your custom output generator should implement `CodeOutputGeneratorInterface`
```php
// MyCustomOutput.php

class MyCustomOutput implements CodeOutputGeneratorInterface {
    // ...
}
```