<?php

namespace ViaCep\Parsers;

use ViaCep\DTO\Address;

interface ParserInterface
{
    /**
     * Parses the response and returns an Address or array of Addresses.
     *
     * @return Address|array<Address>
     */
    public function parse(string $response): Address|array;

    /**
     * Checks if the response can be parsed.
     */
    public function canParse(string $response): bool;
}
