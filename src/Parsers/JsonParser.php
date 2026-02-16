<?php

namespace ViaCep\Parsers;

use RuntimeException;
use ViaCep\DTO\Address;
use ViaCep\Exceptions\CepNotFoundException;

class JsonParser implements ParserInterface
{
    public function parse(string $response): Address|array
    {
        $data = json_decode($response, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid JSON response');
        }

        if (isset($data['erro']) && ($data['erro'] === true || $data['erro'] === 'true')) {
            throw new CepNotFoundException('CEP not found');
        }

        if (isset($data['cep'])) {
            return Address::fromArray($data);
        }

        if (is_array($data)) {
            return array_map(
                fn ($item) => Address::fromArray($item),
                $data
            );
        }

        throw new RuntimeException('Unexpected JSON structure');
    }

    public function canParse(string $response): bool
    {
        json_decode($response);

        return json_last_error() === JSON_ERROR_NONE;
    }
}
