# Fluent ViaCEP Wrapper for Laravel

[![Latest Version on Packagist](https://img.shields.io/packagist/v/eliel-elie/laravel-fluent-viacep.svg?style=flat-square)](https://packagist.org/packages/eliel-elie/laravel-fluent-viacep)
[![Total Downloads](https://img.shields.io/packagist/dt/eliel-elie/laravel-fluent-viacep.svg?style=flat-square)](https://packagist.org/packages/eliel-elie/laravel-fluent-viacep)
[![License](https://img.shields.io/packagist/l/eliel-elie/laravel-fluent-viacep.svg?style=flat-square)](https://packagist.org/packages/eliel-elie/laravel-fluent-viacep)

A modern, fluent, and highly customizable wrapper for the ViaCEP API, designed specifically for Laravel applications. Built with PHP 8.2+ features and following best practices for production-ready Laravel packages.

> **ViaCEP** is a free Brazilian postal code (CEP) lookup service that provides address information based on postal codes or vice-versa.

## ✨ Features

- 🚀 **Fluent Interface**: Intuitive and readable chainable API for building requests
- 📦 **Multiple Formats**: Full support for JSON, XML, Piped, and JSONP response formats
- 💾 **Smart Caching**: Built-in Laravel Cache integration with customizable TTL and cache keys
- 🔄 **Automatic Retries**: Configurable retry mechanism with exponential backoff for resilience
- 🏗️ **Immutable DTO**: Type-safe `Address` object using PHP 8.2+ readonly properties
- 🔍 **Dual Search Modes**: Search by CEP or by full address (State/City/Street)
- 📦 **Bulk Requests**: Fetch multiple CEPs in a single call with automatic error handling
- 🛠️ **CEP Utilities**: Built-in validation, formatting, and cleaning helpers
- 🎨 **Custom Transformers**: Apply custom data transformations via closures
- ⚙️ **Fully Configurable**: Every aspect configurable via config file or method chaining
- 🧪 **Fully Tested**: Comprehensive test coverage using Pest PHP
- 🎯 **Laravel Native**: Built specifically for Laravel 10, 11, and 12
- 🔒 **Type Safe**: Full type hints and return types for IDE autocompletion
- 🌐 **Raw Response Support**: Access raw API responses when needed

## 📋 Requirements

- PHP 8.2 or higher
- Laravel 10.x, 11.x, or 12.x
- Guzzle HTTP Client 7.8+

## 📦 Installation

You can install the package via composer:

```bash
composer require elielelie/laravel-fluent-viacep
```

The service provider will automatically register itself.

You can publish the config file with:

```bash
php artisan vendor:publish --tag="viacep-config"
```

## ⚙️ Configuration

You can publish the config file with:

```bash
php artisan vendor:publish --tag="viacep-config"
```

This is the contents of the published config file (`config/viacep.php`):

```php
return [
    // Base URL for ViaCEP API
    'base_url' => env('VIACEP_BASE_URL', 'https://viacep.com.br/ws'),
    
    // Request timeout in seconds
    'timeout' => env('VIACEP_TIMEOUT', 10),
    
    // Number of retry attempts on failure
    'retry' => env('VIACEP_RETRY', 3),
    
    // Enable/disable caching
    'cache_enabled' => env('VIACEP_CACHE_ENABLED', true),
    
    // Cache TTL in seconds (1 hour default)
    'cache_ttl' => env('VIACEP_CACHE_TTL', 3600),
    
    // Cache key prefix
    'cache_prefix' => env('VIACEP_CACHE_PREFIX', 'viacep'),
    
    // Default response format (json, xml, piped, jsonp)
    'default_format' => env('VIACEP_DEFAULT_FORMAT', 'json'),
];
```

### Environment Variables

Add these to your `.env` file to customize the package behavior:

```env
VIACEP_BASE_URL=https://viacep.com.br/ws
VIACEP_TIMEOUT=10
VIACEP_RETRY=3
VIACEP_CACHE_ENABLED=true
VIACEP_CACHE_TTL=3600
VIACEP_CACHE_PREFIX=viacep
VIACEP_DEFAULT_FORMAT=json
```

## 📖 Usage

### Quick Start

```php
use ViaCep\Facades\ViaCep;

// Simple CEP lookup
$address = ViaCep::cep('01001000')->get();

echo $address->logradouro; // Praça da Sé
echo $address->localidade; // São Paulo
echo $address->uf;         // SP
```

### 🔍 Search Methods

#### Search by CEP (Postal Code)

```php
// Basic search
$address = ViaCep::cep('01001000')->get();

// With formatting
$address = ViaCep::cep('01001-000')->get(); // Automatically cleans formatting

// CEP validation is automatic
try {
    $address = ViaCep::cep('invalid')->get();
} catch (\ViaCep\Exceptions\InvalidCepException $e) {
    // Handle invalid CEP
}
```

#### Search by Address (State/City/Street)

Search for multiple addresses matching your criteria:

```php
// Returns array of Address objects
$addresses = ViaCep::state('SP')
    ->city('São Paulo')
    ->street('Praça da Sé')
    ->get();

foreach ($addresses as $address) {
    echo $address->cep . PHP_EOL;
}
```

**Requirements for address search:**
- `state()`: Required - 2-letter state code (UF)
- `city()`: Required - City name
- `street()`: Required - Minimum 3 characters

### 📦 Bulk Requests

Fetch multiple CEPs efficiently in a single call:

```php
$ceps = ['01001000', '20040-020', '30130-100'];

$results = ViaCep::bulk($ceps)->get();

// Results are indexed by CEP
foreach ($results as $cep => $address) {
    echo "{$cep}: {$address->localidade}" . PHP_EOL;
}
```

**Features:**
- Automatic error handling (invalid CEPs are skipped)
- Maintains cache settings for all requests
- Returns associative array indexed by CEP

### 📄 Response Formats

The package supports all ViaCEP API response formats:

#### JSON (Default)

```php
$address = ViaCep::cep('01001000')->asJson()->get();
// Returns: Address DTO object
```

#### XML

```php
$address = ViaCep::cep('01001000')->asXml()->get();
// Returns: Address DTO object parsed from XML
```

#### Piped Format

```php
$address = ViaCep::cep('01001000')->asPiped()->get();
// Returns: Address DTO object parsed from piped string
```

#### JSONP

```php
$response = ViaCep::cep('01001000')
    ->asJsonp('myCallback')
    ->raw(); // Returns JSONP string

// Or using alias
$response = ViaCep::cep('01001000')
    ->jsonp('handleAddress')
    ->raw();
```

### 🔄 Raw Responses

Get the raw API response without DTO parsing:

```php
// Get raw JSON string
$jsonString = ViaCep::cep('01001000')->asJson()->raw();

// Get raw XML string
$xmlString = ViaCep::cep('01001000')->asXml()->raw();

// Get raw piped string
$pipedString = ViaCep::cep('01001000')->asPiped()->raw();
```

### 💾 Caching

#### Enable Caching with Custom TTL

```php
// Cache for 1 hour (3600 seconds)
$address = ViaCep::cep('01001000')
    ->cache(3600)
    ->get();

// Cache for 24 hours
$address = ViaCep::cep('01001000')
    ->cache(86400)
    ->get();

// With custom cache key
$address = ViaCep::cep('01001000')
    ->cache(3600, 'my-custom-key')
    ->get();
```

#### Disable Caching

```php
// Disable cache for this request only
$address = ViaCep::cep('01001000')
    ->withoutCache()
    ->get();
```

**Cache Key Structure:**
- Default: `{cache_prefix}:md5(url)`
- Custom: `{cache_prefix}:{custom_key}`
- Prefix from config: `viacep` (configurable)

### 🔄 Retry and Timeout Configuration

Control resilience and performance:

```php
$address = ViaCep::cep('01001000')
    ->timeout(5)    // Set timeout to 5 seconds
    ->retry(3)      // Retry up to 3 times on failure
    ->get();

// Combine with caching for maximum resilience
$address = ViaCep::cep('01001000')
    ->timeout(10)
    ->retry(5)
    ->cache(7200)
    ->get();
```

**Default values:**
- Timeout: 10 seconds
- Retry: 3 attempts
- Retry delay: 100ms between attempts

### 🎨 Custom Transformers

Apply custom transformations to the response:

```php
$customData = ViaCep::cep('01001000')
    ->transform(function ($response) {
        $data = json_decode($response, true);
        
        return [
            'postal_code' => $data['cep'],
            'full_address' => "{$data['logradouro']}, {$data['localidade']}",
            'custom_field' => 'my custom value'
        ];
    })
    ->get();

// Use with any format
$transformed = ViaCep::cep('01001000')
    ->asXml()
    ->transform(function ($xmlString) {
        $xml = simplexml_load_string($xmlString);
        return (array) $xml;
    })
    ->get();
```

### 🛠️ CEP Utility Helpers

Built-in static methods for CEP manipulation:

```php
// Validate CEP format
ViaCep::validate('01001-000');  // true
ViaCep::validate('01001000');   // true
ViaCep::validate('12345');      // false

// Format CEP (add hyphen)
ViaCep::formatCep('01001000');  // 01001-000
ViaCep::formatCep('12345');     // 12345 (invalid, returns as-is)

// Clean CEP (remove formatting)
ViaCep::clean('01001-000');     // 01001000
ViaCep::clean('01.001-000');    // 01001000
```

### 🔧 Advanced Configuration

#### Custom Base URL

Override the API base URL (useful for testing):

```php
$address = ViaCep::cep('01001000')
    ->setBaseUrl('https://custom-api.example.com/ws')
    ->get();
```

#### Method Chaining

All configuration methods return `$this` for fluent chaining:

```php
$address = ViaCep::cep('01001000')
    ->asJson()
    ->cache(3600, 'custom-key')
    ->timeout(15)
    ->retry(5)
    ->get();

// Address search with full configuration
$addresses = ViaCep::state('SP')
    ->city('São Paulo')
    ->street('Avenida Paulista')
    ->timeout(20)
    ->cache(7200)
    ->retry(3)
    ->get();
```

## 📦 Address DTO

The `Address` Data Transfer Object provides a type-safe, immutable representation of address data.

### Properties

All properties are **readonly** and publicly accessible:

```php
$address->cep;         // string: CEP (postal code)
$address->logradouro;  // string: Street name
$address->complemento; // string: Complement
$address->bairro;      // string: Neighborhood
$address->localidade;  // string: City name
$address->uf;          // string: State (UF)
$address->ibge;        // string: IBGE code
$address->gia;         // string: GIA code
$address->ddd;         // string: Area code
$address->siafi;       // string: SIAFI code
```

### Methods

#### `getFullAddress(): string`

Returns a complete formatted address:

```php
$address = ViaCep::cep('01001000')->get();
echo $address->getFullAddress();
// Output: "Praça da Sé, lado ímpar, Sé, São Paulo, SP, 01001-000"
```

#### `getFormattedCep(): string`

Returns CEP with formatting (hyphen):

```php
echo $address->getFormattedCep(); // 01001-000
```

#### `getCity(): string`

Alias for `localidade`:

```php
echo $address->getCity(); // Same as $address->localidade
```

#### `getState(): string`

Alias for `uf`:

```php
echo $address->getState(); // Same as $address->uf
```

#### `getStreet(): string`

Alias for `logradouro`:

```php
echo $address->getStreet(); // Same as $address->logradouro
```

#### `getNeighborhood(): string`

Alias for `bairro`:

```php
echo $address->getNeighborhood(); // Same as $address->bairro
```

#### `getIbgeCode(): string`

Returns the IBGE municipal code:

```php
echo $address->getIbgeCode(); // Same as $address->ibge
```

#### `isComplete(): bool`

Checks if the address has all main fields populated:

```php
if ($address->isComplete()) {
    echo "Address is complete";
}
// Checks: logradouro, bairro, localidade, and uf
```

#### `toArray(): array`

Converts the Address to an associative array:

```php
$data = $address->toArray();
/*
[
    'cep' => '01001-000',
    'logradouro' => 'Praça da Sé',
    'complemento' => 'lado ímpar',
    'bairro' => 'Sé',
    'localidade' => 'São Paulo',
    'uf' => 'SP',
    'ibge' => '3550308',
    'gia' => '1004',
    'ddd' => '11',
    'siafi' => '7107'
]
*/
```

#### `toJson(int $options = 0): string`

Converts the Address to JSON:

```php
$json = $address->toJson();
$prettyJson = $address->toJson(JSON_PRETTY_PRINT);
```

#### `__toString(): string`

String representation returns full address:

```php
echo $address; // Calls getFullAddress()
```

### Creating Address Instances

#### From Array

```php
$address = \ViaCep\DTO\Address::fromArray([
    'cep' => '01001-000',
    'logradouro' => 'Praça da Sé',
    'complemento' => 'lado ímpar',
    'bairro' => 'Sé',
    'localidade' => 'São Paulo',
    'uf' => 'SP',
    'ibge' => '3550308',
    'gia' => '1004',
    'ddd' => '11',
    'siafi' => '7107'
]);
```

### JSON Serialization

The Address DTO implements `JsonSerializable`:

```php
$address = ViaCep::cep('01001000')->get();

// Direct JSON encoding
$json = json_encode($address);

// In Laravel responses
return response()->json($address);

// In Laravel collections
$addresses = collect([$address1, $address2]);
return $addresses->toJson();
```

## 🚨 Exception Handling

The package throws specific exceptions for different error scenarios:

### Exception Types

#### `InvalidCepException`

Thrown when CEP format is invalid:

```php
use ViaCep\Exceptions\InvalidCepException;

try {
    $address = ViaCep::cep('invalid')->get();
} catch (InvalidCepException $e) {
    // Handle invalid CEP format
    echo "Invalid CEP: " . $e->getMessage();
}
```

#### `CepNotFoundException`

Thrown when CEP is not found in the database:

```php
use ViaCep\Exceptions\CepNotFoundException;

try {
    $address = ViaCep::cep('99999999')->get();
} catch (CepNotFoundException $e) {
    // Handle CEP not found
    echo "CEP not found";
}
```

#### `UnsupportedFormatException`

Thrown when an unsupported response format is requested:

```php
use ViaCep\Exceptions\UnsupportedFormatException;

try {
    $address = ViaCep::cep('01001000')
        ->format('invalid-format')
        ->get();
} catch (UnsupportedFormatException $e) {
    // Handle unsupported format
}
```

#### `ViaCepException`

Base exception class for all package exceptions:

```php
use ViaCep\Exceptions\ViaCepException;

try {
    $address = ViaCep::cep('01001000')->get();
} catch (ViaCepException $e) {
    // Catches all package-specific exceptions
    Log::error('ViaCEP Error: ' . $e->getMessage());
}
```

### Exception Hierarchy

```
Exception
└── ViaCepException (base)
    ├── InvalidCepException
    ├── CepNotFoundException
    └── UnsupportedFormatException
```

### Best Practices

```php
use ViaCep\Facades\ViaCep;
use ViaCep\Exceptions\InvalidCepException;
use ViaCep\Exceptions\CepNotFoundException;
use Illuminate\Support\Facades\Log;

try {
    $address = ViaCep::cep($userInput)
        ->timeout(5)
        ->retry(2)
        ->get();
        
    // Process address
    return $address;
    
} catch (InvalidCepException $e) {
    // Handle validation error
    return response()->json([
        'error' => 'Invalid CEP format'
    ], 422);
    
} catch (CepNotFoundException $e) {
    // Handle not found
    return response()->json([
        'error' => 'CEP not found'
    ], 404);
    
} catch (\RuntimeException $e) {
    // Handle network/API errors
    Log::error('ViaCEP API Error', ['error' => $e->getMessage()]);
    return response()->json([
        'error' => 'Service temporarily unavailable'
    ], 503);
}
```

## 💡 Usage Examples

### Laravel Controller Example

```php
<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use ViaCep\Facades\ViaCep;
use ViaCep\Exceptions\InvalidCepException;
use ViaCep\Exceptions\CepNotFoundException;

class AddressController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $request->validate([
            'cep' => 'required|string|size:8'
        ]);

        try {
            $address = ViaCep::cep($request->cep)
                ->cache(3600)
                ->retry(3)
                ->get();

            return response()->json([
                'success' => true,
                'data' => $address
            ]);

        } catch (InvalidCepException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Invalid CEP format'
            ], 422);

        } catch (CepNotFoundException $e) {
            return response()->json([
                'success' => false,
                'error' => 'CEP not found'
            ], 404);
        }
    }

    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'state' => 'required|string|size:2',
            'city' => 'required|string|min:3',
            'street' => 'required|string|min:3'
        ]);

        $addresses = ViaCep::state($request->state)
            ->city($request->city)
            ->street($request->street)
            ->cache(7200)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $addresses,
            'count' => count($addresses)
        ]);
    }
}
```

### Laravel Livewire Component

```php
<?php

namespace App\Livewire;

use Livewire\Component;
use ViaCep\Facades\ViaCep;

class AddressForm extends Component
{
    public $cep = '';
    public $address = null;
    public $error = null;

    public function searchCep()
    {
        $this->error = null;
        $this->address = null;

        if (!ViaCep::validate($this->cep)) {
            $this->error = 'Invalid CEP format';
            return;
        }

        try {
            $this->address = ViaCep::cep($this->cep)
                ->cache(3600)
                ->get();
                
        } catch (\Exception $e) {
            $this->error = 'CEP not found';
        }
    }

    public function render()
    {
        return view('livewire.address-form');
    }
}
```

### Laravel Command

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use ViaCep\Facades\ViaCep;

class ImportAddresses extends Command
{
    protected $signature = 'addresses:import {file}';
    protected $description = 'Import addresses from CEP list';

    public function handle()
    {
        $ceps = file($this->argument('file'), FILE_IGNORE_NEW_LINES);

        $this->info("Importing " . count($ceps) . " addresses...");

        $results = ViaCep::bulk($ceps)
            ->cache(86400)
            ->get();

        $this->info("Successfully imported " . count($results) . " addresses");

        foreach ($results as $cep => $address) {
            $this->line("{$cep}: {$address->localidade} - {$address->uf}");
        }

        return Command::SUCCESS;
    }
}
```

### Service Class Pattern

```php
<?php

namespace App\Services;

use ViaCep\Facades\ViaCep;
use ViaCep\DTO\Address;

class AddressService
{
    public function findByCep(string $cep): ?Address
    {
        try {
            return ViaCep::cep($cep)
                ->cache(3600)
                ->retry(3)
                ->get();
        } catch (\Exception $e) {
            report($e);
            return null;
        }
    }

    public function searchAddress(string $state, string $city, string $street): array
    {
        return ViaCep::state($state)
            ->city($city)
            ->street($street)
            ->cache(7200)
            ->get();
    }

    public function validateAndFormat(string $cep): ?string
    {
        if (!ViaCep::validate($cep)) {
            return null;
        }

        return ViaCep::formatCep($cep);
    }
}
```

## 🧪 Testing

The package includes comprehensive tests using Pest PHP.

### Running Tests

```bash
# Run all tests
composer test

# Run with coverage
vendor/bin/pest --coverage

# Run specific test file
vendor/bin/pest tests/Feature/ViaCepClientTest.php
```

### Test Structure

```
tests/
├── Feature/
│   └── ViaCepClientTest.php      # Integration tests
├── Unit/
│   ├── AddressTest.php             # Address DTO tests
│   └── Parsers/
│       ├── JsonParserTest.php
│       ├── XmlParserTest.php
│       ├── PipedParserTest.php
│       └── JsonpParserTest.php
├── Pest.php                        # Pest configuration
└── TestCase.php                    # Base test case
```

### Testing in Your Application

Mock ViaCEP responses in your tests:

```php
use ViaCep\Facades\ViaCep;
use ViaCep\DTO\Address;

test('can create user with address', function () {
    // Mock ViaCEP
    ViaCep::shouldReceive('cep')
        ->with('01001000')
        ->andReturnSelf();
        
    ViaCep::shouldReceive('get')
        ->andReturn(new Address(
            cep: '01001-000',
            logradouro: 'Praça da Sé',
            complemento: 'lado ímpar',
            bairro: 'Sé',
            localidade: 'São Paulo',
            uf: 'SP',
            ibge: '3550308',
            gia: '1004',
            ddd: '11',
            siafi: '7107'
        ));

    // Test your code
    $response = $this->post('/users', [
        'name' => 'John Doe',
        'cep' => '01001000'
    ]);

    $response->assertOk();
});
```

## 🎯 API Reference

### Main Methods

| Method | Parameters | Return | Description |
|--------|-----------|--------|-------------|
| `cep(string $cep)` | CEP string | `self` | Set CEP for search |
| `state(string $uf)` | State code (2 letters) | `self` | Set state for address search |
| `city(string $city)` | City name | `self` | Set city for address search |
| `street(string $street)` | Street name (min 3 chars) | `self` | Set street for address search |
| `bulk(array $ceps)` | Array of CEPs | `self` | Set multiple CEPs for bulk search |
| `get()` | - | `Address\|array` | Execute the request |
| `raw()` | - | `string` | Get raw API response |

### Format Methods

| Method | Parameters | Return | Description |
|--------|-----------|--------|-------------|
| `format(string\|ResponseFormat)` | Format name/enum | `self` | Set response format |
| `asJson()` | - | `self` | Set format to JSON |
| `asXml()` | - | `self` | Set format to XML |
| `asPiped()` | - | `self` | Set format to Piped |
| `asJsonp(string $callback)` | Callback name | `self` | Set format to JSONP |
| `jsonp(string $callback)` | Callback name | `self` | Alias for asJsonp |

### Configuration Methods

| Method | Parameters | Return | Description |
|--------|-----------|--------|-------------|
| `timeout(int $seconds)` | Timeout in seconds | `self` | Set request timeout |
| `retry(int $times)` | Number of retries | `self` | Set retry attempts |
| `cache(int $ttl, ?string $key)` | TTL in seconds, optional key | `self` | Enable caching with TTL |
| `withoutCache()` | - | `self` | Disable caching for request |
| `setBaseUrl(string $url)` | Base URL | `self` | Override API base URL |
| `transform(Closure $transformer)` | Transformer closure | `self` | Apply custom transformation |

### Static Helper Methods

| Method | Parameters | Return | Description |
|--------|-----------|--------|-------------|
| `validate(string $cep)` | CEP string | `bool` | Validate CEP format |
| `formatCep(string $cep)` | CEP string | `string` | Format CEP with hyphen |
| `clean(string $cep)` | CEP string | `string` | Remove CEP formatting |

## 📚 Additional Resources

### ViaCEP API Documentation

- **Official Website**: https://viacep.com.br
- **API Documentation**: https://viacep.com.br/ws/
- **Response Format Examples**: https://viacep.com.br/exemplo/

### Understanding CEP

CEP (Código de Endereçamento Postal) is the Brazilian postal code system:
- Format: `00000-000` (8 digits)
- First 5 digits: Region/district
- Last 3 digits: Specific location

### Response Format Examples

**JSON Format:**
```json
{
  "cep": "01001-000",
  "logradouro": "Praça da Sé",
  "complemento": "lado ímpar",
  "bairro": "Sé",
  "localidade": "São Paulo",
  "uf": "SP",
  "ibge": "3550308",
  "gia": "1004",
  "ddd": "11",
  "siafi": "7107"
}
```

**XML Format:**
```xml
<?xml version="1.0" encoding="UTF-8"?>
<xmlcep>
  <cep>01001-000</cep>
  <logradouro>Praça da Sé</logradouro>
  <complemento>lado ímpar</complemento>
  <bairro>Sé</bairro>
  <localidade>São Paulo</localidade>
  <uf>SP</uf>
  <ibge>3550308</ibge>
  <gia>1004</gia>
  <ddd>11</ddd>
  <siafi>7107</siafi>
</xmlcep>
```

**Piped Format:**
```
cep:01001-000|logradouro:Praça da Sé|complemento:lado ímpar|bairro:Sé|localidade:São Paulo|uf:SP|ibge:3550308|gia:1004|ddd:11|siafi:7107
```

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

### Development Setup

```bash
# Clone the repository
git clone https://github.com/eliel-elie/laravel-fluent-viacep.git

# Install dependencies
composer install

# Run tests
composer test

# Run code style fixer
vendor/bin/pint
```

### Guidelines

- Follow PSR-12 coding standards
- Write tests for new features
- Update documentation
- Keep backward compatibility

## 📝 Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## 🔒 Security

If you discover any security-related issues, please email eliel_elie@hotmail.com instead of using the issue tracker.

## 📄 License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## 👤 Credits

- **Author**: [Eliel Ferreira](https://github.com/eliel-elie)
- **Contributors**: [All Contributors](../../contributors)

## 🙏 Acknowledgments

- [ViaCEP](https://viacep.com.br) for providing the free API service
- Laravel community for the amazing framework
- All contributors who have helped improve this package

## 📞 Support

- **Issues**: [GitHub Issues](https://github.com/eliel-elie/laravel-fluent-viacep/issues)
- **Email**: eliel_elie@hotmail.com

---

<p align="center">
Made with ❤️ for the Laravel community
</p>
