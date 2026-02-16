# Fluent ViaCEP Wrapper for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/fluent-viacep/laravel.svg?style=flat-square)](https://packagist.org/packages/fluent-viacep/laravel)
[![Total Downloads](https://img.shields.io/packagist/dt/fluent-viacep/laravel.svg?style=flat-square)](https://packagist.org/packages/fluent-viacep/laravel)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/fluent-viacep/laravel/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/fluent-viacep/laravel/actions)
[![License](https://img.shields.io/packagist/l/fluent-viacep/laravel.svg?style=flat-square)](https://packagist.org/packages/fluent-viacep/laravel)

A modern, fluent, and highly customizable wrapper for the ViaCEP API, designed specifically for Laravel applications.

## Features

- 🚀 **Fluent Interface**: Intuitive and readable API.
- 📦 **Multiple Formats**: Full support for JSON, XML, Piped, and JSONP.
- 💾 **Integrated Caching**: Built-in support for Laravel Cache with customizable TTL.
- 🔄 **Automatic Retries**: Resilience against temporary network failures.
- 🏗️ **Immutable DTO**: Type-safe address representation using PHP 8.1+ features.
- 🔍 **Address Search**: Search by both CEP and full address details (State/City/Street).
- 🧪 **Fully Tested**: High test coverage using Pest.

## Installation

You can install the package via composer:

```bash
composer require elielelie/laravel-fluent-viacep
```

The service provider will automatically register itself.

You can publish the config file with:

```bash
php artisan vendor:publish --tag="viacep-config"
```

This is the contents of the published config file:

```php
return [
    'base_url' => env('VIACEP_BASE_URL', 'https://viacep.com.br/ws'),
    'timeout' => env('VIACEP_TIMEOUT', 10),
    'retry' => env('VIACEP_RETRY', 3),
    'cache_enabled' => env('VIACEP_CACHE_ENABLED', true),
    'cache_ttl' => env('VIACEP_CACHE_TTL', 3600),
    'cache_prefix' => env('VIACEP_CACHE_PREFIX', 'viacep'),
    'default_format' => env('VIACEP_DEFAULT_FORMAT', 'json'),
];
```

## Usage

### Basic Usage (Search by CEP)

```php
use ViaCep\Facades\ViaCep;

$address = ViaCep::cep('01001000')->get();

echo $address->logradouro; // Praça da Sé
echo $address->localidade; // São Paulo
echo $address->getFormattedCep(); // 01001-000
```

### Search by Address

```php
$addresses = ViaCep::state('SP')
    ->city('São Paulo')
    ->street('Praça da Sé')
    ->get();

foreach ($addresses as $address) {
    echo $address->cep;
}
```

### Response Formats

The package supports all ViaCEP response formats. By default, it returns an `Address` DTO.

```php
// JSON (default)
$address = ViaCep::cep('01001000')->asJson()->get();

// XML
$address = ViaCep::cep('01001000')->asXml()->get();

// Piped
$address = ViaCep::cep('01001000')->asPiped()->get();

// JSONP
$address = ViaCep::cep('01001000')->asJsonp('myCallback')->get();
```

### Caching and Resilience

```php
$address = ViaCep::cep('01001000')
    ->cache(3600) // Cache for 1 hour
    ->retry(3)    // Retry 3 times on failure
    ->timeout(5)  // 5 seconds timeout
    ->get();
```

### Raw Responses

If you need the raw response string instead of a DTO:

```php
$jsonString = ViaCep::cep('01001000')->asJson()->raw();
$xmlString = ViaCep::cep('01001000')->asXml()->raw();
```

### Bulk Requests

```php
$results = ViaCep::bulk(['01001000', '01001001'])->get();
// Returns array of Address DTOs indexed by CEP
```

### CEP Helpers

```php
ViaCep::validate('01001-000'); // true
ViaCep::formatCep('01001000'); // 01001-000
ViaCep::clean('01001-000');   // 01001000
```

## Address DTO

The `Address` object provides several useful methods:

- `$address->getFullAddress()`: Returns full formatted address.
- `$address->getFormattedCep()`: Returns formatted CEP.
- `$address->getCity()`: Alias for `localidade`.
- `$address->getState()`: Alias for `uf`.
- `$address->getStreet()`: Alias for `logradouro`.
- `$address->isComplete()`: Checks if main fields are present.
- `$address->toArray()`: Converts to array.
- `$address->toJson()`: Converts to JSON.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) for details.

## Credits

- [Eliel Ferreira](https://github.com/eliel-elie)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
