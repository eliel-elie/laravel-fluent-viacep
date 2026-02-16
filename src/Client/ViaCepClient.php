<?php

namespace ViaCep\Client;

use Closure;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use ViaCep\DTO\Address;
use ViaCep\Enums\ResponseFormat;
use ViaCep\Exceptions\InvalidCepException;
use ViaCep\Parsers\JsonParser;
use ViaCep\Parsers\JsonpParser;
use ViaCep\Parsers\ParserInterface;
use ViaCep\Parsers\PipedParser;
use ViaCep\Parsers\XmlParser;

class ViaCepClient
{
    protected string $baseUrl;

    protected ResponseFormat $format = ResponseFormat::JSON;

    protected ?string $jsonpCallback = null;

    protected bool $rawResponse      = false;

    protected ?Closure $transformer  = null;

    protected int $timeout;

    protected int $retryTimes;

    protected ?int $cacheTtl         = null;

    protected ?string $cacheKey      = null;

    protected bool $useCache         = true;

    // Search parameters
    protected ?string $cep           = null;

    protected ?string $state         = null;

    protected ?string $city          = null;

    protected ?string $street        = null;

    protected array $bulkCeps        = [];

    public function __construct()
    {
        $this->baseUrl    = config('viacep.base_url', 'https://viacep.com.br/ws');
        $this->timeout    = config('viacep.timeout', 10);
        $this->retryTimes = config('viacep.retry', 3);

        if (config('viacep.cache_enabled', true)) {
            $this->cacheTtl = config('viacep.cache_ttl', 3600);
        }

        $defaultFormat    = config('viacep.default_format', 'json');
        $this->format     = ResponseFormat::tryFrom($defaultFormat) ?: ResponseFormat::JSON;
    }

    /**
     * Set the response format.
     */
    public function format(string|ResponseFormat $format): self
    {
        $this->format = is_string($format)
            ? ResponseFormat::from($format)
            : $format;

        return $this;
    }

    /**
     * Set format to JSON.
     */
    public function asJson(): self
    {
        return $this->format(ResponseFormat::JSON);
    }

    /**
     * Set format to XML.
     */
    public function asXml(): self
    {
        return $this->format(ResponseFormat::XML);
    }

    /**
     * Set format to Piped.
     */
    public function asPiped(): self
    {
        return $this->format(ResponseFormat::PIPED);
    }

    /**
     * Set format to JSONP.
     */
    public function asJsonp(string $callback = 'callback'): self
    {
        $this->format        = ResponseFormat::JSONP;
        $this->jsonpCallback = $callback;

        return $this;
    }

    /**
     * Alias for asJsonp.
     */
    public function jsonp(string $callback): self
    {
        return $this->asJsonp($callback);
    }

    /**
     * Set the CEP for search.
     */
    public function cep(string $cep): self
    {
        $clean     = preg_replace('/\D/', '', $cep);

        if (! $this->isValidCep($clean)) {
            throw new InvalidCepException("Invalid CEP format: {$cep}");
        }

        $this->cep = $clean;

        return $this;
    }

    /**
     * Set the state for address search.
     */
    public function state(string $uf): self
    {
        $this->state = $uf;

        return $this;
    }

    /**
     * Set the city for address search.
     */
    public function city(string $cidade): self
    {
        $this->city = $cidade;

        return $this;
    }

    /**
     * Set the street for address search.
     */
    public function street(string $logradouro): self
    {
        if (strlen($logradouro) < 3) {
            throw new InvalidCepException('Street name must have at least 3 characters');
        }

        $this->street = $logradouro;

        return $this;
    }

    /**
     * Set multiple CEPs for search.
     */
    public function bulk(array $ceps): self
    {
        $this->bulkCeps = [];

        foreach ($ceps as $cep) {
            $clean = preg_replace('/\D/', '', $cep);

            if ($this->isValidCep($clean)) {
                $this->bulkCeps[] = $clean;
            }
        }

        return $this;
    }

    /**
     * Set timeout in seconds.
     */
    public function timeout(int $seconds): self
    {
        $this->timeout = $seconds;

        return $this;
    }

    /**
     * Set number of retries.
     */
    public function retry(int $times): self
    {
        $this->retryTimes = $times;

        return $this;
    }

    /**
     * Set cache TTL in seconds.
     */
    public function cache(int $ttl, ?string $key = null): self
    {
        $this->cacheTtl = $ttl;
        $this->cacheKey = $key;
        $this->useCache = true;

        return $this;
    }

    /**
     * Disable cache for this request.
     */
    public function withoutCache(): self
    {
        $this->useCache = false;

        return $this;
    }

    /**
     * Return raw response without parsing.
     */
    public function raw(): string
    {
        $this->rawResponse = true;

        return $this->executeRequest();
    }

    /**
     * Set custom transformer.
     */
    public function transform(Closure $transformer): self
    {
        $this->transformer = $transformer;

        return $this;
    }

    /**
     * Execute the request.
     */
    public function get(): Address|array|string
    {
        if (! empty($this->bulkCeps)) {
            return $this->executeBulkRequest();
        }

        $response = $this->executeRequest();

        if ($this->rawResponse) {
            return $response;
        }

        if ($this->transformer) {
            return ($this->transformer)($response);
        }

        return $this->parseResponse($response);
    }

    /**
     * Execute a single request.
     */
    protected function executeRequest(): string
    {
        $url      = $this->buildUrl();
        $cacheKey = $this->buildCacheKey($url);

        if ($this->useCache && $this->cacheTtl && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $response = Http::timeout($this->timeout)
            ->retry($this->retryTimes, 100)
            ->get($url);

        if (! $response->successful()) {
            throw new \RuntimeException("Request failed with status {$response->status()}");
        }

        $body     = $response->body();

        if ($this->useCache && $this->cacheTtl) {
            Cache::put($cacheKey, $body, $this->cacheTtl);
        }

        return $body;
    }

    /**
     * Execute multiple requests.
     */
    protected function executeBulkRequest(): array
    {
        $results = [];

        foreach ($this->bulkCeps as $cep) {
            try {
                $client        = new self;
                $client->format($this->format)
                    ->timeout($this->timeout)
                    ->retry($this->retryTimes);

                if ($this->useCache && $this->cacheTtl) {
                    $client->cache($this->cacheTtl);
                }

                $results[$cep] = $client->cep($cep)->get();
            } catch (Exception $e) {
                // Ignore errors in bulk and continue
                continue;
            }
        }

        return $results;
    }

    /**
     * Build the request URL.
     */
    protected function buildUrl(): string
    {
        $endpoint = $this->format->endpoint();

        if ($this->cep) {
            $url = "{$this->baseUrl}/{$this->cep}/{$endpoint}/";
        } elseif ($this->state && $this->city && $this->street) {
            $url = "{$this->baseUrl}/{$this->state}/{$this->city}/{$this->street}/{$endpoint}/";
        } else {
            throw new \RuntimeException('Either CEP or State/City/Street must be provided');
        }

        if ($this->format === ResponseFormat::JSONP && $this->jsonpCallback) {
            $url .= "?callback={$this->jsonpCallback}";
        }

        return $url;
    }

    /**
     * Build cache key.
     */
    protected function buildCacheKey(string $url): string
    {
        if ($this->cacheKey) {
            return config('viacep.cache_prefix', 'viacep') . ':' . $this->cacheKey;
        }

        return config('viacep.cache_prefix', 'viacep') . ':' . md5($url);
    }

    /**
     * Parse the response.
     */
    protected function parseResponse(string $response): Address|array
    {
        $parser = $this->getParser();

        return $parser->parse($response);
    }

    /**
     * Return the appropriate parser.
     */
    protected function getParser(): ParserInterface
    {
        return match ($this->format) {
            ResponseFormat::JSON  => new JsonParser,
            ResponseFormat::XML   => new XmlParser,
            ResponseFormat::PIPED => new PipedParser,
            ResponseFormat::JSONP => new JsonpParser,
        };
    }

    /**
     * Validate CEP format.
     */
    public static function validate(string $cep): bool
    {
        $clean = preg_replace('/\D/', '', $cep);

        return preg_match('/^\d{8}$/', $clean) === 1;
    }

    /**
     * Format CEP (00000-000).
     */
    public static function formatCep(string $cep): string
    {
        $clean = preg_replace('/\D/', '', $cep);

        if (strlen($clean) !== 8) {
            return $cep;
        }

        return substr($clean, 0, 5) . '-' . substr($clean, 5);
    }

    /**
     * Remove CEP formatting.
     */
    public static function clean(string $cep): string
    {
        return preg_replace('/\D/', '', $cep);
    }

    /**
     * Check if CEP is valid.
     */
    protected function isValidCep(string $cep): bool
    {
        return preg_match('/^\d{8}$/', $cep) === 1;
    }
}
