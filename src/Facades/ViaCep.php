<?php

namespace ViaCep\Facades;

use Illuminate\Support\Facades\Facade;
use ViaCep\Client\ViaCepClient;
use ViaCep\DTO\Address;
use ViaCep\Enums\ResponseFormat;

/**
 * @method static ViaCepClient         format(string|ResponseFormat $format)
 * @method static ViaCepClient         asJson()
 * @method static ViaCepClient         asXml()
 * @method static ViaCepClient         asPiped()
 * @method static ViaCepClient         asJsonp(string $callback = 'callback')
 * @method static ViaCepClient         jsonp(string $callback)
 * @method static ViaCepClient         cep(string $cep)
 * @method static ViaCepClient         state(string $uf)
 * @method static ViaCepClient         city(string $cidade)
 * @method static ViaCepClient         street(string $logradouro)
 * @method static ViaCepClient         bulk(array $ceps)
 * @method static ViaCepClient         timeout(int $seconds)
 * @method static ViaCepClient         retry(int $times)
 * @method static ViaCepClient         cache(int $ttl, string $key = null)
 * @method static ViaCepClient         withoutCache()
 * @method static string               raw()
 * @method static ViaCepClient         transform(\Closure $transformer)
 * @method static Address|array|string get()
 * @method static bool                 validate(string $cep)
 * @method static string               formatCep(string $cep)
 * @method static string               clean(string $cep)
 *
 * @see ViaCepClient
 */
class ViaCep extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'viacep';
    }
}
