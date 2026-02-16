<?php

namespace ViaCep\Parsers;

use RuntimeException;
use ViaCep\DTO\Address;

class PipedParser implements ParserInterface
{
    public function parse(string $response): Address|array
    {
        $parts = explode('|', trim($response));
        $data  = [];

        foreach ($parts as $part) {
            if (! str_contains($part, ':')) {
                continue;
            }

            [$key, $value]    = explode(':', $part, 2);
            $data[trim($key)] = trim($value);
        }

        if (empty($data)) {
            throw new RuntimeException('Invalid piped response');
        }

        return Address::fromArray($data);
    }

    public function canParse(string $response): bool
    {
        return str_contains($response, '|')
            && str_contains($response, ':');
    }
}
